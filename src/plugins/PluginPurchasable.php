<?php

namespace craftnet\plugins;

use craftnet\base\Purchasable;
use yii\base\InvalidConfigException;

/**
 * @property Plugin $plugin
 */
abstract class PluginPurchasable extends Purchasable
{
    // Properties
    // =========================================================================

    /**
     * @var int The plugin ID
     */
    public $pluginId;

    /**
     * @var Plugin|null
     */
    private $_plugin;

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
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['pluginId'], 'required'];
        $rules[] = [['pluginId'], 'number', 'integerOnly' => true];
        return $rules;
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
            throw new InvalidConfigException('Invalid plugin ID: ' . $this->pluginId);
        }
        return $this->_plugin = $plugin;
    }

    /**
     * @param Plugin|null $plugin
     */
    public function setPlugin(Plugin $plugin = null)
    {
        $this->_plugin = $plugin;
    }
}
