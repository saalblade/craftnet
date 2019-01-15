<?php

namespace craftnet\base;

use craft\commerce\base\PurchasableInterface as CommercePurchasableInterface;
use craftnet\errors\LicenseNotFoundException;

interface PurchasableInterface extends CommercePurchasableInterface
{
    /**
     * Returns the "type" value that should be included in toArray().
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Returns the license associated with a given key.
     *
     * @param string $key
     * @return LicenseInterface
     * @throws LicenseNotFoundException if $key is missing
     */
    public function getLicenseByKey(string $key): LicenseInterface;
}
