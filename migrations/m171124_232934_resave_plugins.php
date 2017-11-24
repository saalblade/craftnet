<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\queue\jobs\ResaveElements;
use craftcom\plugins\Plugin;

/**
 * m171124_232934_resave_plugins migration.
 */
class m171124_232934_resave_plugins extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Re-disable any Craft-licensed plugins so we can verify that they have a LICENSE.md
        $plugins = Plugin::find()
            ->license('craft')
            ->all();

        foreach ($plugins as $plugin) {
            $plugin->pendingApproval = true;
            $plugin->enabled = false;
            Craft::$app->elements->saveElement($plugin);
        }

        // Save the rest later, so we get the new license keyword
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
        echo "m171124_232934_resave_plugins cannot be reverted.\n";
        return false;
    }
}
