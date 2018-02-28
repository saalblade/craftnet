<?php

namespace craftcom\cms;

use Craft;
use craft\base\Element;
use craft\commerce\base\Purchasable;
use craft\commerce\base\PurchasableInterface;
use craft\db\Query;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\ArrayHelper;
use yii\base\InvalidConfigException;


class CmsRenewal extends Purchasable
{
    // Static
    // =========================================================================

    /**
     * @return string
     */
    public static function displayName(): string
    {
        return 'CMS Renewal';
    }

    /**
     * @return CmsRenewalQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new CmsRenewalQuery(static::class);
    }

    // Properties
    // =========================================================================

    /**
     * @var int The CMS edition ID
     */
    public $editionId;

    /**
     * @var float The renewal price
     */
    public $price;

    // Public Methods
    // =========================================================================

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['editionId', 'price'], 'required'];
        $rules[] = [['editionId'], 'number', 'integerOnly' => true];
        $rules[] = [['price'], 'number'];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew)
    {
        $data = [
            'id' => $this->id,
            'editionId' => $this->editionId,
            'price' => $this->price,
        ];

        if ($isNew) {
            Craft::$app->getDb()->createCommand()
                ->insert('craftcom_cmsrenewals', $data, false)
                ->execute();
        } else {
            Craft::$app->getDb()->createCommand()
                ->update('craftcom_cmsrenewals', $data, ['id' => $this->id], [], false)
                ->execute();
        }

        parent::afterSave($isNew);
    }

    /**
     * Returns the CMS edition associated with the renewal.
     *
     * @return CmsEdition
     * @throws InvalidConfigException if [[editionId]] is invalid
     */
    public function getEdition(): CmsEdition
    {
        if ($this->editionId === null) {
            throw new InvalidConfigException('CMS renewal is missing its edition ID');
        }
        if (($edition = CmsEdition::findOne($this->editionId)) === null) {
            throw new InvalidConfigException('Invalid edition ID: '.$this->editionId);
        };
        return $edition;
    }

    /**
     * @inheritdoc
     */
    public function getIsAvailable(): bool
    {
        return parent::getIsAvailable() && $this->price;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return $this->getEdition()->name.' Renewal';
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
        return $this->getEdition()->getSku().'-RENEWAL';
    }
}
