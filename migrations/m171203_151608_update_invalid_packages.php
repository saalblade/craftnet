<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craftcom\composer\jobs\UpdatePackage;

/**
 * m171203_151608_update_invalid_packages migration.
 */
class m171203_151608_update_invalid_packages extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $invalids = (new Query())
            ->select(['p.name'])
            ->distinct()
            ->from(['craftcom_packageversions pv'])
            ->innerJoin('craftcom_packages p', '[[p.id]] = [[pv.packageId]]')
            ->where(['valid' => false])
            ->column($this->db);

        $queue = Craft::$app->getQueue();
        foreach ($invalids as $name) {
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
        echo "m171203_151608_update_invalid_packages cannot be reverted.\n";
        return false;
    }
}
