<?php

namespace craftcom\plugins;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class PluginQuery extends ElementQuery
{
    /**
     * @var string|string[]|null The handle(s) that the resulting plugins must have.
     */
    public $handle;

    public function __construct($elementType, array $config = [])
    {
        // Default orderBy
        if (!isset($config['orderBy'])) {
            $config['orderBy'] = 'name';
        }

        parent::__construct($elementType, $config);
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

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('craftcom_plugins');

        $this->query->select([
            'craftcom_plugins.developerId',
            'craftcom_plugins.packageId',
            'craftcom_plugins.iconId',
            'craftcom_plugins.packageName',
            'craftcom_plugins.repository',
            'craftcom_plugins.name',
            'craftcom_plugins.handle',
            'craftcom_plugins.price',
            'craftcom_plugins.renewalPrice',
            'craftcom_plugins.license',
            'craftcom_plugins.shortDescription',
            'craftcom_plugins.longDescription',
            'craftcom_plugins.documentationUrl',
            'craftcom_plugins.changelogUrl',
            'craftcom_plugins.latestVersion',
        ]);

        if ($this->handle) {
            $this->subQuery->andWhere(Db::parseParam('craftcom_plugins.handle', $this->handle));
        }

        return parent::beforePrepare();
    }

}
