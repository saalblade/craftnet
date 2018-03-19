<?php

namespace craftnet\developers;

use Craft;
use craft\db\Query;
use craft\elements\User;
use craft\helpers\Db;
use craftnet\errors\InaccessibleFundsException;
use craftnet\errors\InsufficientFundsException;
use craftnet\errors\MissingStripeAccountException;
use Stripe\Error\Base as StripeError;
use Stripe\Stripe;
use Stripe\Transfer;
use yii\base\BaseObject;
use yii\base\InvalidArgumentException;
use yii\db\Expression;

class FundsManager extends BaseObject
{
    /**
     * @var User|Developer
     */
    public $developer;

    /**
     * @var string
     */
    private $_lockName;

    /**
     * @var bool
     * @see _lockFunds()
     * @see _unlockFunds()
     */
    private $_lockLevel = 0;

    /**
     * @inheritdoc
     */
    public function __construct(User $developer, array $config = [])
    {
        $this->developer = $developer;
        $this->_lockName = 'funds:'.$developer->id;
        parent::__construct($config);
    }

    /**
     * Returns the developer’s current balance.
     */
    public function getBalance(): float
    {
        return (new Query())
            ->select(['balance'])
            ->from('craftnet_developers')
            ->where(['id' => $this->developer->id])
            ->scalar();
    }

    /**
     * Credits the developer's account.
     *
     * @param string $note the transaction note
     * @param float $credit the credit amount (not including any fees that need to be removed)
     * @param float|null $fee the total fees that should be deducted from the credit amount
     * @throws InvalidArgumentException if $credit or $fee are negative or 0
     */
    public function credit(string $note, float $credit, float $fee = null)
    {
        if ($credit <= 0) {
            throw new InvalidArgumentException('Invalid credit amount: '.$credit);
        }

        if ($fee !== null && $fee <= 0) {
            throw new InvalidArgumentException('Invalid fee amount: '.$credit);
        }

        $this->_addTransaction($note, $credit, null, $fee);
    }

    /**
     * Debits the developer's account.
     *
     * @param string $note the transaction note
     * @param float $debit the credit amount (not including any fees that need to be removed)
     * @throws InvalidArgumentException if $debit is negative or 0
     * @throws InaccessibleFundsException if the developer's funds could not be locked
     */
    public function debit(string $note, float $debit)
    {
        if ($debit <= 0) {
            throw new InvalidArgumentException('Invalid debit amount: '.$debit);
        }

        // no double-dipping
        if (!$this->_lockFunds()) {
            throw new InaccessibleFundsException();
        }

        $this->_addTransaction($note, null, $debit);
        $this->_unlockFunds();
    }

    /**
     * Credits the developer's account based on their total sales in a given order,
     * and then attempts to transfer the funds to their Stripe account.
     *
     * @param string $orderNumber
     * @param string $chargeId
     * @param float $credit the credit amount (not including any fees that need to be removed)
     * @param float|null $fee the total fees that should be deducted from the credit amount
     * @throws InvalidArgumentException if $credit or $fee are negative or 0
     */
    public function processOrder(string $orderNumber, string $chargeId, float $credit, float $fee = null)
    {
        $e = null;

        // no double-dipping
        if (!$this->_lockFunds(5)) {
            $e = new InaccessibleFundsException();
        }

        // credit the account
        $this->credit("Payment received for order {$orderNumber}", $credit, $fee);

        // only attempt the transfer if we could get a lock
        if (!$e) {
            // figure out how much we're going to transfer (credit amount minus fee, but no more than whatever they actually have in their account)
            $balance = $this->getBalance();
            $transferAmount = min($credit - $fee, $balance);

            // don't transfer if they're in the red
            if ($transferAmount <= 0) {
                $e = new InsufficientFundsException($balance);
            } else {
                try {
                    $this->_transferFunds("Funds transferred for order {$orderNumber}", $transferAmount, ['source_transaction' => $chargeId]);
                } catch (StripeError $e) {
                    // Something unexpected happened on Stripe's end
                    Craft::$app->getErrorHandler()->logException($e);
                } catch (\Throwable $e) {
                }
            }
        }

        if ($e) {
            $this->_addTransaction("Failed transfer: {$e->getMessage()}");
        }

        // unlock the funds
        $this->_unlockFunds();
    }

    /**
     * Locks the developer's funds so no other FundsManager instance can deduct funds.
     *
     * @param int $timeout
     * @return bool
     */
    private function _lockFunds(int $timeout = 0): bool
    {
        if ($this->_lockLevel === 0) {
            if (!Craft::$app->getMutex()->acquire($this->_lockName, $timeout)) {
                return false;
            }
        }
        $this->_lockLevel++;
        return true;
    }

    /**
     * Unlocks the developer's funds so other FundsManager instances can deduct funds again.
     */
    private function _unlockFunds()
    {
        if ($this->_lockLevel === 0) {
            return;
        }
        $this->_lockLevel--;
        if ($this->_lockLevel === 0) {
            Craft::$app->getMutex()->release($this->_lockName);
        }
    }

    /**
     * Attempts to transfer funds to the developer's Stripe account.
     *
     * @param string $note
     * @param float $amount
     * @param array $params
     * @throws InvalidArgumentException if $credit or $fee are negative or 0
     * @throws MissingStripeAccountException if the developer doesn't have a Stripe account
     * @throws InaccessibleFundsException if the developer's funds could not be locked
     * @throws InsufficientFundsException if the developer doesn't have enough funds for the transfer
     * @throws StripeError if anything goes wrong on Stripe's end
     */
    private function _transferFunds(string $note, float $amount, array $params = [])
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Invalid transfer amount: '.$amount);
        }

        if (($stripeAccount = $this->developer->stripeAccount) === null) {
            throw new MissingStripeAccountException();
        }

        // no double-dipping
        if (!$this->_lockFunds()) {
            throw new InaccessibleFundsException();
        }

        // make sure the funds are there
        $balance = $this->getBalance();
        if ($balance < $amount) {
            $this->_unlockFunds();
            throw new InsufficientFundsException($balance);
        }

        Stripe::setApiKey(getenv('STRIPE_API_KEY'));

        $params += [
            'amount' => round($amount * 100),
            'currency' => 'usd',
            'destination' => $stripeAccount,
        ];

        try {
            Transfer::create($params);
        } catch (StripeError $e) {
            $this->_unlockFunds();
            throw $e;
        }

        $this->debit($note, $amount);
        $this->_unlockFunds();
    }

    /**
     * Adds a new transaction to the developer's ledger, and updates their current account balance.
     *
     * @param string $note
     * @param float|null $credit
     * @param float|null $debit
     * @param float|null $fee
     */
    private function _addTransaction(string $note, float $credit = null, float $debit = null, float $fee = null)
    {
        $db = Craft::$app->getDb();

        if ($credit !== null) {
            $operator = '+';
            $adjustment = $credit - ($fee ?? 0);
        } else if ($debit !== null) {
            $operator = '-';
            $adjustment = $debit;
        } else {
            $adjustment = false;
        }

        if ($adjustment !== false) {
            $db->createCommand()
                ->update('craftnet_developers',
                    [
                        'balance' => new Expression("[[balance]] {$operator} :adjustment", [':adjustment' => $adjustment])
                    ],
                    [
                        'id' => $this->developer->id
                    ], [], false)
                ->execute();
        }

        $ledgerSql = <<<SQL
insert into {{craftnet_developerledger}} (
    [[developerId]],
    [[note]],
    [[credit]],
    [[debit]],
    [[fee]],
    [[balance]],
    [[dateCreated]]
) values (
    :developerId,
    :note,
    :credit,
    :debit,
    :fee,
    (
        select [[balance]]
        from {{craftnet_developers}}
        where [[id]] = :developerId
    ),
    :dateCreated
)
SQL;

        $db->createCommand($ledgerSql, [
            'developerId' => $this->developer->id,
            'note' => $note,
            'credit' => $credit,
            'debit' => $debit,
            'fee' => $fee,
            'dateCreated' => Db::prepareDateForDb(new \DateTime()),
        ])->execute();
    }
}
