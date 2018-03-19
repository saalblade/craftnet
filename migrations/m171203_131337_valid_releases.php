<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craftnet\composer\jobs\UpdatePackage;

/**
 * m171203_131337_valid_releases migration.
 */
class m171203_131337_valid_releases extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Place migration code here...
        $this->addColumn('craftcom_packageversions', 'valid', $this->boolean()->defaultValue(true)->notNull());

        // Update indexes while we're at it
        $this->createIndex(null, 'craftcom_packageversions', ['packageId', 'valid']);
        $this->createIndex(null, 'craftcom_packageversions', ['packageId', 'stability', 'valid']);
        $this->createIndex(null, 'craftcom_packageversions', ['packageId', 'normalizedVersion', 'valid']);

        // Force update any packages with missing `dist` columns
        $invalids = (new Query())
            ->select(['p.name'])
            ->distinct()
            ->from(['craftcom_packageversions pv'])
            ->innerJoin('craftcom_packages p', '[[p.id]] = [[pv.packageId]]')
            ->where(['dist' => null])
            ->column($this->db);

        $duplicates = (new Query())
            ->select(['p.name'])
            ->from(['craftcom_packageversions pv'])
            ->innerJoin('craftcom_packages p', '[[p.id]] = [[pv.packageId]]')
            ->groupBy(['p.id', 'pv.normalizedVersion'])
            ->having('count(*) > 1')
            ->where(['not', ['p.name' => $invalids]])
            ->column($this->db);

        $packages = array_merge($invalids, $duplicates);
        $queue = Craft::$app->getQueue();
        foreach ($packages as $name) {
            $queue->push(new UpdatePackage([
                'name' => $name,
                'force' => true
            ]));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171203_131337_valid_releases cannot be reverted.\n";
        return false;
    }
}
