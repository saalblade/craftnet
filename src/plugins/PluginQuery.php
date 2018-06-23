<?php

namespace craftnet\plugins;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use yii\db\Connection;

/**
 * @method Plugin[]|array all($db = null)
 * @method Plugin|array|null one($db = null)
 * @method Plugin|array|null nth(int $n, Connection $db = null)
 */
class PluginQuery extends ElementQuery
{
    /**
     * @var string|string[]|null The handle(s) that the resulting plugins must have.
     */
    public $handle;

    /**
     * @var string|string[]|null The license(s) that the resulting plugins must have.
     */
    public $license;

    /**
     * @var int|int[]|null The category ID(s) that the resulting plugins must have.
     */
    public $categoryId;

    /**
     * @var int|int[]|null The user ID(s) that the resulting plugins’ developers must have.
     */
    public $developerId;

    /**
     * @var int|int[]|null The Composer package ID(s) that the resulting plugins must be associated with.
     */
    public $packageId;

    /**
     * @var bool|null Whether the matching plugins must have (or must not have) a latest version.
     */
    public $hasLatestVersion;

    /**
     * @inheritdoc
     */
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

    /**
     * Sets the [[license]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function license($value)
    {
        $this->license = $value;
        return $this;
    }

    /**
     * Sets the [[categoryId]] property.
     *
     * @param int|int[]|null $value The property value
     *
     * @return static self reference
     */
    public function categoryId($value)
    {
        $this->categoryId = $value;
        return $this;
    }

    /**
     * Sets the [[developerId]] property.
     *
     * @param int|int[]|null $value The property value
     *
     * @return static self reference
     */
    public function developerId($value)
    {
        $this->developerId = $value;
        return $this;
    }

    /**
     * Sets the [[packageId]] property.
     *
     * @param int|int[]|null $value The property value
     *
     * @return static self reference
     */
    public function packageId($value)
    {
        $this->packageId = $value;
        return $this;
    }

    /**
     * Sets the [[hasLatestVersion]] property.
     *
     * @param bool $value The property value
     *
     * @return static self reference
     */
    public function hasLatestVersion($value = true)
    {
        $this->hasLatestVersion = $value;
        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('craftnet_plugins');

        $this->query->select([
            'craftnet_plugins.developerId',
            'craftnet_plugins.packageId',
            'craftnet_plugins.iconId',
            'craftnet_plugins.packageName',
            'craftnet_plugins.repository',
            'craftnet_plugins.name',
            'craftnet_plugins.handle',
            'craftnet_plugins.price',
            'craftnet_plugins.renewalPrice',
            'craftnet_plugins.license',
            'craftnet_plugins.shortDescription',
            'craftnet_plugins.longDescription',
            'craftnet_plugins.documentationUrl',
            'craftnet_plugins.changelogPath',
            'craftnet_plugins.latestVersion',
            'craftnet_plugins.activeInstalls',
            'craftnet_plugins.pendingApproval',
            'craftnet_plugins.keywords',
            'craftnet_plugins.dateApproved',
        ]);

        if ($this->handle) {
            $this->subQuery->andWhere(Db::parseParam('craftnet_plugins.handle', $this->handle));
        }

        if ($this->license) {
            $this->subQuery->andWhere(Db::parseParam('craftnet_plugins.license', $this->license));
        }

        if ($this->developerId) {
            $this->subQuery->andWhere(Db::parseParam('craftnet_plugins.developerId', $this->developerId));
        }

        if ($this->packageId) {
            $this->subQuery->andWhere(Db::parseParam('craftnet_plugins.packageId', $this->packageId));
        }

        if ($this->categoryId) {
            $this->subQuery
                ->innerJoin(['craftnet_plugincategories pc'], '[[pc.pluginId]] = [[elements.id]]')
                ->andWhere(Db::parseParam('pc.categoryId', $this->categoryId));
        }

        if ($this->hasLatestVersion === true) {
            $this->subQuery->andWhere(['not', ['craftnet_plugins.latestVersion' => null]]);
        } else if ($this->hasLatestVersion === false) {
            $this->subQuery->andWhere(['craftnet_plugins.latestVersion' => null]);
        }

        return parent::beforePrepare();
    }

    /**
     * @inheritdoc
     */
    protected function statusCondition(string $status)
    {
        if ($status === Plugin::STATUS_PENDING) {
            return ['elements.enabled' => false, 'craftnet_plugins.pendingApproval' => true];
        }

        return parent::statusCondition($status);
    }
}
