<?php

namespace craftcom\developers;

use Craft;
use craft\commerce\elements\Order;
use craft\elements\User;
use craft\helpers\Db;
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
     * @inheritdoc
     */
    public function __construct(User $developer, array $config = [])
    {
        $this->developer = $developer;
        parent::__construct($config);
    }

    /**
     * Credits a developer's account.
     *
     * @param string $note the transaction note
     * @param float $credit the credit amount (not including any fees that need to be removed)
     * @param float|null $fee the total fees that should be deducted from the credit amount
     * @throws InvalidArgumentException if $credit or $fee are negative or 0
     * @return $this
     */
    public function credit(string $note, float $credit, float $fee = null): self
    {
        if ($credit <= 0) {
            throw new InvalidArgumentException('Invalid credit amount: '.$credit);
        }

        if ($fee !== null && $fee <= 0) {
            throw new InvalidArgumentException('Invalid fee amount: '.$credit);
        }

        $this->_addTransaction($note, $credit, null, $fee);
        return $this;
    }

    /**
     * Debits a developer's account.
     *
     * @param string $note the transaction note
     * @param float $debit the credit amount (not including any fees that need to be removed)
     * @throws InvalidArgumentException if $debit is negative or 0
     * @return $this
     */
    public function debit(string $note, float $debit): self
    {
        if ($debit <= 0) {
            throw new InvalidArgumentException('Invalid debit amount: '.$debit);
        }

        $this->_addTransaction($note, null, $debit);
        return $this;
    }

    /**
     * Attempts to issue a payout to the developer.
     *
     * @param string $note the transaction note
     * @param float $amount the payout amount
     * @throws InvalidArgumentException if $amount is negative or 0
     * @return $this
     */
    public function transfer(string $note, float $amount): self
    {
        // no double-dipping
        $mutex = Craft::$app->getMutex();
        $lockName = 'funds:'.$this->developer->id;
        if (!$mutex->acquire($lockName, 1)) {
            Craft::warning("Aborted funds transfer for {$this->developer->id}: could not acquire lock on funds", __METHOD__);
            return $this;
        }

        // attempt to transfer the funds
        if ($this->_createTransfer($amount)) {
            $this->debit($note, $amount);
        };

        // unlock the funds
        $mutex->release($lockName);

        return $this;
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
     * @return $this
     */
    public function processOrder(string $orderNumber, string $chargeId, float $credit, float $fee = null): self
    {
        // no double-dipping
        $mutex = Craft::$app->getMutex();
        $lockName = 'funds:'.$this->developer->id;
        $locked = $mutex->acquire($lockName, 1);

        // credit the account
        $this->credit("Payment received for order {$orderNumber}", $credit, $fee);

        // if we couldn't get a lock, then we're done.
        if (!$locked) {
            Craft::warning("Aborted funds transfer for {$this->developer->id}: could not acquire lock on funds", __METHOD__);
            return $this;
        }

        // attempt to transfer the funds
        $transferAmount = $credit - $fee;
        if ($this->_createTransfer($transferAmount, ['source_transaction' => $chargeId])) {
            $this->debit("Funds transferred for order {$orderNumber}", $transferAmount);
        };

        // unlock the funds
        $mutex->release($lockName);

        return $this;
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
            $adjustment = $credit - ($fee  ?? 0);
        } else {
            $operator = '-';
            $adjustment = $debit;
        }

        $db->createCommand()
            ->update('craftcom_developers',
                [
                    'balance' => new Expression("[[balance]] {$operator} :adjustment", [':adjustment' => $adjustment])
                ],
                [
                    'id' => $this->developer->id
                ], [], false)
            ->execute();

        $ledgerSql = <<<SQL
insert into {{craftcom_developerledger}} (
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
        from {{craftcom_developers}}
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

    /**
     * Attempts to transfer funds to the developer's Stripe account.
     *
     * @param float $amount
     * @param array $params
     * @return bool whether the transfer was successful
     */
    private function _createTransfer(float $amount, array $params = []): bool
    {
        if (($stripeAccount = $this->developer->stripeAccount) === null) {
            Craft::warning("Aborted funds transfer for {$this->developer->id}: developer has no Stripe account", __METHOD__);
            return false;
        }

        Stripe::setApiKey(getenv('STRIPE_API_KEY'));

        $params += [
            'amount' => round($amount * 100),
            'currency' => 'usd',
            'destination' => $stripeAccount,
        ];

        try {
            Transfer::create($params);
        } catch (\Throwable $e) {
            Craft::warning("Unsuccessful funds transfer for {$this->developer->id}: {$e->getMessage()}", __METHOD__);
            return false;
        }

        return true;
    }
}
