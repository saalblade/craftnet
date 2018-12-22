<?php

namespace craftnet\base;

use craft\commerce\base\Purchasable as CommercePurchasable;

/**
 * @property-read string $type
 */
abstract class Purchasable extends CommercePurchasable implements PurchasableInterface
{
    /**
     * @inheritdoc
     */
    public function attributes()
    {
        $names = parent::attributes();
        $names[] = 'type';
        return $names;
    }
}
