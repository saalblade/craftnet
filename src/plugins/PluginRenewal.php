<?php

namespace craftcom\plugins;

use Craft;
use craft\base\Element;
use craft\commerce\base\Purchasable;
use craft\commerce\base\PurchasableInterface;
use craft\db\Query;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\ArrayHelper;
use yii\base\InvalidConfigException;


class PluginRenewal extends Purchasable
{
    // Static
    // =========================================================================

    /**
     * @return string
     */
    public static function displayName(): string
    {
        return 'Plugin Renewal';
    }

    /**
     * @return PluginRenewalQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new PluginRenewalQuery(static::class);
    }

    // Properties
    // =========================================================================

    /**
     * @var int The plugin ID
     */
    public $pluginId;

    /**
     * @var int The plugin edition ID
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
        $rules[] = [['pluginId', 'editionId', 'price'], 'required'];
        $rules[] = [['pluginId', 'editionId'], 'number', 'integerOnly' => true];
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
            'pluginId' => $this->pluginId,
            'editionId' => $this->editionId,
            'price' => $this->price,
        ];

        if ($isNew) {
            Craft::$app->getDb()->createCommand()
                ->insert('craftcom_pluginrenewals', $data, false)
                ->execute();
        } else {
            Craft::$app->getDb()->createCommand()
                ->update('craftcom_pluginrenewals', $data, ['id' => $this->id], [], false)
                ->execute();
        }

        parent::afterSave($isNew);
    }

    /**
     * Returns the plugin associated with the renewal.
     *
     * @return Plugin
     * @throws InvalidConfigException if [[pluginId]] is invalid
     */
    public function getPlugin(): Plugin
    {
        if ($this->pluginId === null) {
            throw new InvalidConfigException('Plugin renewal is missing its plugin ID');
        }
        if (($plugin = Plugin::find()->id($this->pluginId)->status(null)->one()) === null) {
            throw new InvalidConfigException('Invalid plugin ID: '.$this->pluginId);
        };
        return $plugin;
    }

    /**
     * Returns the plugin edition associated with the renewal.
     *
     * @return PluginEdition
     * @throws InvalidConfigException if [[editionId]] is invalid
     */
    public function getEdition(): PluginEdition
    {
        if ($this->editionId === null) {
            throw new InvalidConfigException('Plugin renewal is missing its edition ID');
        }
        if (($edition = PluginEdition::findOne($this->editionId)) === null) {
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
        // todo: include the edition name when we start supporting editions
        return $this->getPlugin()->name.' Renewal';
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
