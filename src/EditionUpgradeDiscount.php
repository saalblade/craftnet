<?php

namespace craftcom;

use craft\commerce\adjusters\Discount;
use craft\commerce\base\AdjusterInterface;
use craft\commerce\elements\Order;
use craft\commerce\models\OrderAdjustment;
use craftcom\cms\CmsEdition;
use craftcom\cms\CmsLicenseManager;

class EditionUpgradeDiscount implements AdjusterInterface
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
            if ($purchasable instanceof CmsEdition) {
                $licenseKey = $lineItem->options['licenseKey'];
                $license = Module::getInstance()->getCmsLicenseManager()->getLicenseByKey($licenseKey);
                if ($license->edition === CmsLicenseManager::EDITION_CLIENT && $purchasable->handle === CmsLicenseManager::EDITION_PRO) {
                    $adjustments[] = new OrderAdjustment([
                        'orderId' => $order->id,
                        'lineItemId' => $lineItem->id,
                        'type' => Discount::ADJUSTMENT_TYPE,
                        'name' => 'Upgrade Discount',
                        'description' => 'Craft Pro Upgrade Discount',
                        'amount' => -199,
                    ]);
                }
            }
        }

        // todo: add plugin upgrade adjustments

        return $adjustments;
    }
}
