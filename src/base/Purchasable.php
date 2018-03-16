<?php

namespace craftcom\base;

use craft\commerce\base\Purchasable as CommercePurchasable;

/**
 * @property-read string $type
 */
abstract class Purchasable extends CommercePurchasable
{
    /**
     * Returns the "type" value that should be included in toArray().
     * @return string
     */
    abstract public function getType(): string;

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
