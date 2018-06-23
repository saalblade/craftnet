<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\queue\jobs\ResaveElements;
use craftnet\plugins\Plugin;

/**
 * m171122_010332_resave_plugins migration.
 */
class m171122_010332_resave_plugins extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        Craft::$app->getQueue()->push(new ResaveElements([
            'elementType' => Plugin::class,
            'criteria' => ['status' => null],
        ]));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171122_010332_resave_plugins cannot be reverted.\n";
        return false;
    }
}
