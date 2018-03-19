<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craftnet\composer\jobs\UpdatePackage;

/**
 * m171116_202033_update_all_plugins migration.
 */
class m171116_202033_update_all_plugins extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $names = (new Query())
            ->select(['packageName'])
            ->from(['craftcom_plugins'])
            ->where(['not', ['changelogPath' => null]])
            ->column($this->db);

        $queue = Craft::$app->getQueue();

        foreach ($names as $name) {
            $queue->push(new UpdatePackage([
                'name' => $name,
                'force' => true,
            ]));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171116_202033_update_all_plugins cannot be reverted.\n";
        return false;
    }
}
