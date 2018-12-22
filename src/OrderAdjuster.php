<?php

namespace craftnet;

use craft\commerce\adjusters\Discount;
use craft\commerce\base\AdjusterInterface;
use craft\commerce\elements\Order;
use craft\commerce\models\LineItem;
use craft\commerce\models\OrderAdjustment;
use craft\helpers\DateTimeHelper;
use craftnet\base\EditionInterface;
use craftnet\errors\LicenseNotFoundException;

class OrderAdjuster implements AdjusterInterface
{
    // Constants
    // =========================================================================

    /**
     * The discount adjustment type.
     */
    const ADJUSTMENT_TYPE = 'discount';

    // Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function adjust(Order $order): array
    {
        $adjustments = [];

        foreach ($order->getLineItems() as $lineItem) {
            $purchasable = $lineItem->getPurchasable();
            if ($purchasable instanceof EditionInterface) {
                $this->_adjustForEdition($order, $lineItem, $purchasable, $adjustments);
            }
        }

        return $adjustments;
    }

    /**
     * Sets order adjustments for an edition.
     *
     * @param Order $order
     * @param LineItem $lineItem
     * @param EditionInterface $edition
     * @param array $adjustments
     * @throws LicenseNotFoundException
     */
    private function _adjustForEdition(Order $order, LineItem $lineItem, EditionInterface $edition, array &$adjustments)
    {
        // Existing license?
        $options = $lineItem->getOptions();
        $license = $oldExpiryDate = null;

        if (strpos($options['licenseKey'], 'new:') !== 0) {
            $license = $edition->getLicenseByKey($options['licenseKey']);
            if ($license->getIsExpirable()) {
                $oldExpiryDate = $license->getExpiryDate()->setTimezone(new \DateTimeZone('UTC'));
            }
        }

        $renewal = $edition->getRenewal();
        $nextYear = $expiryDate = (new \DateTime())->modify('+1 year');

        // If the line item specifies an expiry date, go with that
        if (!empty($options['expiryDate'])) {
            $expiryDate = max($expiryDate, DateTimeHelper::toDateTime($options['expiryDate']));
        }

        // If it's an existing license, make sure the expiry date is at least the current one
        if ($oldExpiryDate) {
            $expiryDate = max($expiryDate, $oldExpiryDate);
        }

        $expiryDate->setTimezone(new \DateTimeZone('UTC'));

        // If the expiry date is over a year from now, charge for it
        if ($expiryDate > $nextYear) {
            $paidRenewalYears = $this->_diffInYears($nextYear, $expiryDate);

            $adjustments[] = new OrderAdjustment([
                'orderId' => $order->id,
                'lineItemId' => $lineItem->id,
                'type' => Discount::ADJUSTMENT_TYPE,
                'name' => 'Updates until ' . $expiryDate->format('Y-m-d'),
                'amount' => round($renewal->getPrice() * $paidRenewalYears, 2),
                'sourceSnapshot' => [
                    'renewalPrice' => $renewal->getPrice(),
                    'expiryDate' => $expiryDate->format(\DateTime::ATOM),
                    'paidRenewalYears' => $paidRenewalYears,
                ],
            ]);
        }

        // Is this an upgrade?
        if ($license) {
            $oldEdition = $license->getEdition();

            $editionUpgradeDiscount = min($oldEdition->getPrice(), $edition->getPrice());

            if ($editionUpgradeDiscount > 0) {
                $adjustments[] = new OrderAdjustment([
                    'orderId' => $order->id,
                    'lineItemId' => $lineItem->id,
                    'type' => Discount::ADJUSTMENT_TYPE,
                    'name' => 'Edition upgrade discount',
                    'amount' => -$editionUpgradeDiscount,
                    'sourceSnapshot' => [
                        'oldEdition' => $oldEdition->getHandle(),
                        'oldEditionPrice' => $oldEdition->getPrice(),
                    ],
                ]);
            }

            // Was the old expiration date over a year away?
            if ($oldExpiryDate && $oldExpiryDate > $nextYear) {
                $oldRenewal = $oldEdition->getRenewal();
                $renewalUpgradeDiscount = min($oldRenewal->getPrice(), $renewal->getPrice());

                if ($renewalUpgradeDiscount > 0) {
                    $oldPaidRenewalYears = $this->_diffInYears($nextYear, $oldExpiryDate);

                    $adjustments[] = new OrderAdjustment([
                        'orderId' => $order->id,
                        'lineItemId' => $lineItem->id,
                        'type' => Discount::ADJUSTMENT_TYPE,
                        'name' => 'Renewal upgrade discount',
                        'amount' => -round($renewalUpgradeDiscount * $oldPaidRenewalYears, 2),
                        'sourceSnapshot' => [
                            'oldRenewalPrice' => $oldRenewal->getPrice(),
                            'oldExpiryDate' => $oldExpiryDate->format(\DateTime::ATOM),
                            'oldPaidRenewalYears' => $oldPaidRenewalYears,
                        ],
                    ]);
                }
            }
        }
    }

    /**
     * Calculates the difference in years between two dates.
     *
     * @param \DateTime $d1
     * @param \DateTime $d2
     * @return float
     */
    private function _diffInYears(\DateTime $d1, \DateTime $d2): float
    {
        $d1 = new \DateTime($d1->format('Y-m-d'), new \DateTimeZone('UTC'));
        $d2 = new \DateTime($d2->format('Y-m-d'), new \DateTimeZone('UTC'));
        $diff = $d2->diff($d1);
        $years = $diff->y;

        // Calculate the difference in % into the year each date is
        if ($diff->m !== 0 || $diff->d !== 0) {
            $d1Pct = $d1->format('z') / ($d1->format('L') ? 365 : 364);
            $d2Pct = $d2->format('z') / ($d2->format('L') ? 365 : 364);
            if ($d2Pct >= $d1Pct) {
                $years += $d2Pct - $d1Pct;
            } else {
                $years += 1 - ($d1Pct - $d2Pct);
            }
        }

        return $years;
    }
}
