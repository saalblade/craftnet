<?php

namespace craftcom\cms;

use Craft;
use craft\commerce\base\Purchasable;
use craft\commerce\elements\Order;
use craft\commerce\models\LineItem;
use craft\elements\db\ElementQueryInterface;
use craftcom\errors\LicenseNotFoundException;
use craftcom\Module;


class CmsEdition extends Purchasable
{
    // Static
    // =========================================================================

    /**
     * @return string
     */
    public static function displayName(): string
    {
        return 'CMS Edition';
    }

    /**
     * @return CmsEditionQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new CmsEditionQuery(static::class);
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
        return $this->name;
    }

    // Public Methods
    // =========================================================================

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
            'name' => $this->name,
            'handle' => $this->handle,
            'price' => $this->price,
            'renewalPrice' => $this->renewalPrice,
        ];

        if ($isNew) {
            Craft::$app->getDb()->createCommand()
                ->insert('craftcom_cmseditions', $data, false)
                ->execute();
        } else {
            Craft::$app->getDb()->createCommand()
                ->update('craftcom_cmseditions', $data, ['id' => $this->id], [], false)
                ->execute();
        }

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return 'Craft '.$this->name;
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
        return 'CRAFT-'.strtoupper($this->handle);
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
        try {
            Module::getInstance()->getCmsLicenseManager()->upgradeLicense($lineItem->options['licenseKey'], $this, $lineItem->id);
        } catch (LicenseNotFoundException $e) {
            Craft::error("Could not upgrade Craft license {$lineItem->options['licenseKey']} for order {$order->number}: {$e->getMessage()}");
            Craft::$app->getErrorHandler()->logException($e);
        }

        parent::afterOrderComplete($order, $lineItem);
    }
}
