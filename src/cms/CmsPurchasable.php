<?php

namespace craftnet\cms;

use craftnet\base\LicenseInterface;
use craftnet\base\Purchasable;
use craftnet\Module;

abstract class CmsPurchasable extends Purchasable
{
    /**
     * @inheritdoc
     */
    public function getLicenseByKey(string $key): LicenseInterface
    {
        return Module::getInstance()->getCmsLicenseManager()->getLicenseByKey($key);
    }
}
