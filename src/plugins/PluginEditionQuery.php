<?php

namespace craftcom\plugins;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use yii\db\Connection;

/**
 * @method PluginEdition[]|array all($db = null)
 * @method PluginEdition|array|null one($db = null)
 * @method PluginEdition|array|null nth(int $n, Connection $db = null)
 */
class PluginEditionQuery extends ElementQuery
{
    /**
     * @var int|int[]|null The plugin IDs(s) that the resulting editions must be associated with.
     */
    public $pluginId;

    /**
     * @var string|string[]|null The handle(s) that the resulting editions must have.
     */
    public $handle;

    /**
     * @var bool|null Whether only commercial or non-commercial editions should be returned. Null means all editions.
     */
    public $commercial;

    public function __construct($elementType, array $config = [])
    {
        // Default orderBy
        if (!isset($config['orderBy'])) {
            $config['orderBy'] = 'price';
        }

        parent::__construct($elementType, $config);
    }

    /**
     * Sets the [[pluginId]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function pluginId($value)
    {
        $this->pluginId = $value;
        return $this;
    }

    /**
     * Sets the [[handle]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function handle($value)
    {
        $this->handle = $value;
        return $this;
    }

    /**
     * Sets the [[commercial]] property.
     *
     * @param bool|null $value The property value
     *
     * @return static self reference
     */
    public function commercial($value = true)
    {
        $this->commercial = $value;
        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('craftcom_plugineditions');

        $this->query->select([
            'craftcom_plugineditions.pluginId',
            'craftcom_plugineditions.name',
            'craftcom_plugineditions.handle',
            'craftcom_plugineditions.price',
            'craftcom_plugineditions.renewalPrice',
        ]);

        if ($this->pluginId) {
            $this->subQuery->andWhere(Db::parseParam('craftcom_plugineditions.pluginId', $this->pluginId));
        }

        if ($this->handle) {
            $this->subQuery->andWhere(Db::parseParam('craftcom_plugineditions.handle', $this->handle));
        }

        if ($this->commercial === true) {
            $this->subQuery->andWhere(['not', ['craftcom_plugineditions.price' => 0]]);
        } else if ($this->commercial === false) {
            $this->subQuery->andWhere(['craftcom_plugineditions.price' => 0]);
        }

        return parent::beforePrepare();
    }
}
