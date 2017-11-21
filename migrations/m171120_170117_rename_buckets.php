<?php

namespace craft\contentmigrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

/**
 * m171120_170117_rename_buckets migration.
 */
class m171120_170117_rename_buckets extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $buckets = [
            'avatars' => 'avatars.craftcms',
            'icons' => 'plugin-icons.craftcms',
            'screenshots' => 'plugin-screenshots.craftcms'
        ];

        $volumes = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['volumes'])
            ->where(['handle' => array_keys($buckets)])
            ->all();

        foreach ($volumes as $volume) {
            $settings = Json::decode($volume['settings']);
            $settings['bucket'] = $buckets[$volume['handle']];
            $this->update('volumes', [
                'url' => "http://{$settings['bucket']}.s3.amazonaws.com/",
                'settings' => Json::encode($settings),
            ], ['id' => $volume['id']], [], false);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171120_170117_rename_buckets cannot be reverted.\n";
        return false;
    }
}
