<?php

namespace craft\contentmigrations;

use craft\db\Migration;
use craftnet\plugins\Plugin;
use yii\db\Expression;

/**
 * m180314_193826_dateApproved migration.
 */
class m180314_193826_dateApproved extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftcom_plugins', 'dateApproved', $this->dateTime()->null());
        $this->createIndex(null, 'craftcom_plugins', ['dateApproved']);

        $enabledPluginIds = Plugin::find()->ids();

        $this->update('craftcom_plugins', [
            'dateApproved' => new Expression('[[dateCreated]]')
        ], ['id' => $enabledPluginIds], [], false);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180314_193826_dateApproved cannot be reverted.\n";
        return false;
    }
}
