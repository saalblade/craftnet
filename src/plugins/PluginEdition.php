<?php

namespace craftcom\plugins;

use Craft;
use craft\base\Element;
use craft\commerce\base\Purchasable;
use craft\commerce\base\PurchasableInterface;
use craft\commerce\elements\Order;
use craft\commerce\models\LineItem;
use craft\db\Query;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\ArrayHelper;
use craftcom\errors\LicenseNotFoundException;
use craftcom\Module;
use yii\base\InvalidConfigException;


/**
 * Class PluginEdition
 *
 * @property Plugin $plugin
 */
class PluginEdition extends Purchasable
{
    // Static
    // =========================================================================

    /**
     * @return string
     */
    public static function displayName(): string
    {
        return 'Plugin Edition';
    }

    /**
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
                ->from(['craftcom_plugineditions'])
                ->where(['id' => ArrayHelper::getColumn($sourceElements, 'id')]);
            return ['elementType' => Plugin::class, 'map' => $query->all()];
        }

        return parent::eagerLoadingMap($sourceElements, $handle);
    }

    // Properties
    // =========================================================================

    /**
     * @var int The plugin ID
     */
    public $pluginId;

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
     * @var Plugin|null
     */
    private $_plugin;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        return [
            'plugin',
        ];
    }

    /**
     * @param string $handle
     * @param array $elements
     */
    public function setEagerLoadedElements(string $handle, array $elements)
    {
        if ($handle === 'plugin') {
            $this->_plugin = $elements[0] ?? null;
        } else {
            parent::setEagerLoadedElements($handle, $elements);
        }
    }

    /**
     * @return Plugin
     * @throws InvalidConfigException
     */
    public function getPlugin(): Plugin
    {
        if ($this->_plugin !== null) {
            return $this->_plugin;
        }
        if ($this->pluginId === null) {
            throw new InvalidConfigException('Plugin edition is missing its plugin ID');
        }
        if (($plugin = Plugin::find()->id($this->pluginId)->status(null)->one()) === null) {
            throw new InvalidConfigException('Invalid plugin ID: '.$this->pluginId);
        }
        return $this->_plugin = $plugin;
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['pluginId', 'name', 'handle', 'price', 'renewalPrice'], 'required',];
        $rules[] = [['pluginId'], 'number', 'integerOnly' => true];
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
                ->insert('craftcom_plugineditions', $data, false)
                ->execute();
        } else {
            Craft::$app->getDb()->createCommand()
                ->update('craftcom_plugineditions', $data, ['id' => $this->id], [], false)
                ->execute();
        }

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        // todo: include $this->name when we start supporting editions
        $plugin = $this->getPlugin();
        return $plugin->getDeveloperName().' '.$plugin->name;
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
        try {
            Module::getInstance()->getPluginLicenseManager()->upgradeLicense($lineItem->options['licenseKey'], $this, $lineItem->id);
        } catch (LicenseNotFoundException $e) {
            Craft::error("Could not upgrade plugin license {$lineItem->options['licenseKey']} for order {$order->number}: {$e->getMessage()}");
            Craft::$app->getErrorHandler()->logException($e);
        }

        parent::afterOrderComplete($order, $lineItem);
    }
}
