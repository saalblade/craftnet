<?php

namespace craftnet\plugins;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use yii\db\Connection;

/**
 * @method PluginRenewal[]|array all($db = null)
 * @method PluginRenewal|array|null one($db = null)
 * @method PluginRenewal|array|null nth(int $n, Connection $db = null)
 */
class PluginRenewalQuery extends ElementQuery
{
    /**
     * @var int|int[]|null The plugin edition IDs(s) that the resulting renewals must be associated with.
     */
    public $editionId;

    /**
     * Sets the [[editionId]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function editionId($value)
    {
        $this->editionId = $value;
        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('craftnet_pluginrenewals');

        $this->query->select([
            'craftnet_pluginrenewals.pluginId',
            'craftnet_pluginrenewals.editionId',
        ]);

        if ($this->editionId) {
            $this->subQuery->andWhere(Db::parseParam('craftnet_pluginrenewals.editionId', $this->editionId));
        }

        return parent::beforePrepare();
    }
}
