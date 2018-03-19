<?php

namespace craftnet\plugins;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\models\LineItem;
use craft\commerce\Plugin as Commerce;
use craft\db\Query;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\ArrayHelper;
use craftnet\base\PluginPurchasable;
use craftnet\errors\LicenseNotFoundException;
use craftnet\Module;
use yii\base\Exception;

/**
 * @property-read string $fullName
 */
class PluginEdition extends PluginPurchasable
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Plugin Edition';
    }

    /**
     * @inheritdoc
     * @return PluginEditionQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new PluginEditionQuery(static::class);
    }

    /**
     * @param array $sourceElements
     * @param string $handle
     *
     * @return array|bool|false
     */
    public static function eagerLoadingMap(array $sourceElements, string $handle)
    {
        if ($handle === 'plugin') {
            $query = (new Query())
                ->select(['id as source', 'pluginId as target'])
                ->from(['craftnet_plugineditions'])
                ->where(['id' => ArrayHelper::getColumn($sourceElements, 'id')]);
            return ['elementType' => Plugin::class, 'map' => $query->all()];
        }

        return parent::eagerLoadingMap($sourceElements, $handle);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        return [
            [
                'key' => '*',
                'label' => 'All editions',
            ],
            [
                'key' => 'commercial',
                'label' => 'Commercial editions',
                'criteria' => ['commercial' => true],
            ],
            [
                'key' => 'free',
                'label' => 'Free editions',
                'criteria' => ['commercial' => false],
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        return [
            'name' => ['label' => 'Name'],
            'developer' => ['label' => 'Developer'],
            'price' => ['label' => 'Price'],
            'renewalPrice' => ['label' => 'Renewal Price'],
        ];
    }

    // Properties
    // =========================================================================

    /**
     * @var string The edition name
     */
    public $name;

    /**
     * @var string The edition handle (personal, client, pro)
     */
    public $handle;

    /**
     * @var float The edition price
     */
    public $price;

    /**
     * @var float The edition renewal price
     */
    public $renewalPrice;

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->getFullName();
        } catch (\Throwable $e) {
            return $this->name;
        }
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'plugin-edition';
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        $names = parent::attributes();
        $names[] = 'fullName';
        return $names;
    }

    /**
     * Returns the full plugin edition name (including the plugin name).
     *
     * @return string
     */
    public function getFullName(): string
    {
        return "{$this->getPlugin()->name} ({$this->name})";
    }

    /**
     * @inheritdoc
     */
    public function getThumbUrl(int $size)
    {
        return $this->getPlugin()->getThumbUrl($size);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSearchableAttributes(): array
    {
        return [
            'fullName',
        ];
    }

    /**
     * @param string $handle
     * @param array $elements
     */
    public function setEagerLoadedElements(string $handle, array $elements)
    {
        if ($handle === 'plugin') {
            $this->setPlugin($elements[0] ?? null);
        } else {
            parent::setEagerLoadedElements($handle, $elements);
        }
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['name', 'handle', 'price', 'renewalPrice'], 'required'];
        $rules[] = [['price', 'renewalPrice'], 'number'];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew)
    {
        $data = [
            'id' => $this->id,
            'pluginId' => $this->pluginId,
            'name' => $this->name,
            'handle' => $this->handle,
            'price' => $this->price,
            'renewalPrice' => $this->renewalPrice,
        ];

        if ($isNew) {
            Craft::$app->getDb()->createCommand()
                ->insert('craftnet_plugineditions', $data, false)
                ->execute();
        } else {
            Craft::$app->getDb()->createCommand()
                ->update('craftnet_plugineditions', $data, ['id' => $this->id], [], false)
                ->execute();
        }

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function getIsAvailable(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        // todo: include $this->name when we start supporting editions
        return $this->getPlugin()->name;
    }

    /**
     * @inheritdoc
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @inheritdoc
     */
    public function getSku(): string
    {
        return strtoupper($this->getPlugin()->handle.'-'.$this->handle);
    }

    /**
     * @inheritdoc
     */
    public function getLineItemRules(LineItem $lineItem): array
    {
        // todo: this isn't getting called
        return [
            [
                ['options'],
                function() use ($lineItem) {
                    return isset($lineItem->options['licenseKey']);
                },
                'skipOnEmpty' => false,
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterOrderComplete(Order $order, LineItem $lineItem)
    {
        $this->_upgradeOrderLicense($order, $lineItem);
        parent::afterOrderComplete($order, $lineItem);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            'name' => 'Name',
            'price' => 'price',
            'renewalPrice' => 'renewalPrice',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'developer':
                return $this->getPlugin()->getDeveloperName();
            case 'price':
            case 'renewalPrice':
                return Craft::$app->getFormatter()->asCurrency($this->$attribute, 'USD', [], [], true);
            default:
                return parent::tableAttributeHtml($attribute);
        }
    }

    // Private Methods
    // =========================================================================

    /**
     * @param Order $order
     * @param LineItem $lineItem
     */
    private function _upgradeOrderLicense(Order $order, LineItem $lineItem)
    {
        $manager = Module::getInstance()->getPluginLicenseManager();

        // is this for an existing plugin license?
        $isNew = (strncmp($lineItem->options['licenseKey'], 'new:', 4) === 0);
        if (!$isNew) {
            try {
                $license = $manager->getLicenseByKey($this->getPlugin()->handle, $lineItem->options['licenseKey']);
            } catch (LicenseNotFoundException $e) {
                Craft::error("Could not upgrade plugin license {$lineItem->options['licenseKey']} for order {$order->number}: {$e->getMessage()}");
                Craft::$app->getErrorHandler()->logException($e);
                return;
            }
        } else {
            // chop off "new:"
            $key = substr($lineItem->options['licenseKey'], 4);

            // was a Craft license specified?
            if (!empty($lineItem->options['cmsLicenseKey'])) {
                try {
                    $cmsLicense = Module::getInstance()->getCmsLicenseManager()->getLicenseByKey($lineItem->options['cmsLicenseKey']);
                } catch (LicenseNotFoundException $e) {
                    Craft::error("Could not associate new plugin license with Craft license {$lineItem->options['cmsLicenseKey']} for order {$order->number}: {$e->getMessage()}");
                    Craft::$app->getErrorHandler()->logException($e);
                    $cmsLicense = null;
                }
            }

            // create the new license
            $license = new PluginLicense([
                'pluginId' => $this->pluginId,
                'cmsLicenseId' => $cmsLicense->id ?? null,
                'plugin' => $this->getPlugin()->handle,
                'key' => $key,
            ]);
        }

        $oldEmail = $license->email;
        $oldEdition = $license->edition;

        $license->editionId = $this->id;
        $license->edition = $this->handle;
        $license->expired = false;

        // If this was placed before April 4, or it was bought with a coupon created before April 4, set the license to non-expirable
        if (time() < 1522800000) {
            $license->expirable = false;
        } else if ($order->couponCode) {
            $discount = Commerce::getInstance()->getDiscounts()->getDiscountByCode($order->couponCode);
            if ($discount && $discount->dateCreated->getTimestamp() < 1522800000) {
                $license->expirable = false;
            }
        }

        if (isset($lineItem->options['autoRenew'])) {
            $license->autoRenew = $lineItem->options['autoRenew'];
        }

        // if the license doesn't have an owner yet, reassign it to the order's customer
        if (!$license->ownerId) {
            $license->email = $order->getEmail();
            $license->ownerId = $order->getCustomer()->userId;
        }

        try {
            // save the license
            if (!$manager->saveLicense($license)) {
                Craft::error("Could not save plugin license {$license->key} for order {$order->number}: ".implode(', ', $license->getErrorSummary(true)));
                return;
            }

            // relate the license to the line item
            Craft::$app->getDb()->createCommand()
                ->insert('craftnet_pluginlicenses_lineitems', [
                    'licenseId' => $license->id,
                    'lineItemId' => $lineItem->id,
                ], false)
                ->execute();

            // update the license history
            if ($isNew) {
                $note = "created by {$license->email}";
            } else {
                $note = "upgraded to {$license->edition}";
                if ($license->email !== $oldEmail) {
                    $note .= " and reassigned to {$license->email}";
                }
            }
            $manager->addHistory($license->id, "{$note} per order {$order->number}");
        } catch (Exception $e) {
            Craft::error("Could not save plugin license {$license->key} for order {$order->number}: {$e->getMessage()}");
            Craft::$app->getErrorHandler()->logException($e);
        }
    }
}
