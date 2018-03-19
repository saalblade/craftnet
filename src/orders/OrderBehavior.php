<?php

namespace craftnet\orders;

use craft\commerce\elements\Order;
use craft\commerce\records\Transaction as TransactionRecord;
use craft\elements\User;
use craftnet\base\PluginPurchasable;
use craftnet\developers\Developer;
use yii\base\Behavior;

/**
 * @property Order $owner
 */
class OrderBehavior extends Behavior
{
    /**
     * @inheritdoc
     */
    public function events()
    {
        // todo: we should probably be listening for a transaction event here
        return [
            Order::EVENT_AFTER_COMPLETE_ORDER => [$this, 'afterComplete'],
        ];
    }

    /**
     * Handles post-order-complete stuff.
     */
    public function afterComplete()
    {
        if (!$this->owner->getIsPaid()) {
            return;
        }

        // See if any plugin licenses were purchased/renewed
        /** @var User[]|Developer[] $developers */
        $developers = [];
        $developerTotals = [];
        foreach ($this->owner->getLineItems() as $lineItem) {
            $purchasable = $lineItem->getPurchasable();
            if ($purchasable instanceof PluginPurchasable) {
                $plugin = $purchasable->getPlugin();
                $developerId = $plugin->developerId;
                if (!isset($developers[$developerId])) {
                    $developers[$developerId] = $plugin->getDeveloper();
                    $developerTotals[$developerId] = $lineItem->total;
                } else {
                    $developerTotals[$developerId] += $lineItem->total;
                }
            }
        }

        if (empty($developers)) {
            return;
        }

        // find the first successful transaction on the order
        // todo: if we change the event here, then we will need to be more careful about which transaction we're looking for
        $transaction = null;
        foreach ($this->owner->getTransactions() as $t) {
            if ($t->status === TransactionRecord::STATUS_SUCCESS) {
                $transaction = $t;
                break;
            }
        }
        if (!$transaction) {
            return;
        }

        // Try transferring funds to them
        foreach ($developers as $developerId => $developer) {
            // ignore if this is us
            if ($developer->username === 'pixelandtonic') {
                continue;
            }

            // figure out our 20% fee (up to 2 decimals)
            $total = $developerTotals[$developerId];
            $fee = floor($total * 20) / 100;
            $developer->getFundsManager()->processOrder($this->owner->number, $transaction->reference, $total, $fee);
        }
    }
}
