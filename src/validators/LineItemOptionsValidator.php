<?php

namespace craftnet\validators;

use craft\commerce\models\LineItem;
use craftnet\base\Purchasable;
use craftnet\base\RenewalInterface;
use craftnet\errors\LicenseNotFoundException;
use yii\validators\Validator;

class LineItemOptionsValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public $skipOnEmpty = false;

    /**
     * @var Purchasable The edition or renewal
     */
    public $purchasable;

    /**
     * @inheritdoc
     */
    public function validateAttribute($lineItem, $attribute)
    {
        /** @var LineItem $lineItem */
        $options = $lineItem->getOptions();

        // make sure a license key is set
        if (!isset($options['licenseKey'])) {
            $lineItem->addError('options', 'A license key is required.');
            return;
        }

        $licenseKey = $options['licenseKey'];

        // is this a new license key?
        if (strpos($licenseKey, 'new:') === 0) {
            if ($this->purchasable instanceof RenewalInterface) {
                $lineItem->addError('options', 'License key is invalid.');
            }
            return;
        }

        // make sure it's a valid license key
        try {
            $license = $this->purchasable->getLicenseByKey($licenseKey);
        } catch (LicenseNotFoundException $e) {
            $lineItem->addError('options', 'License key is invalid.');
            return;
        }

        // not possible to renew non-expiring licenses
        if ($this->purchasable instanceof RenewalInterface && !$license->getIsExpirable()) {
            $lineItem->addError('options', 'License key references a non-expiring license.');
        }
    }
}
