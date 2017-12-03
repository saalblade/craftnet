<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craftcom\composer\jobs\UpdatePackage;

/**
 * m171203_174013_fix_changelog_paths migration.
 */
class m171203_174013_fix_changelog_paths extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $plugins = (new Query())
            ->select(['id', 'packageName', 'changelogPath'])
            ->from(['craftcom_plugins'])
            ->where(['like', 'changelogPath', 'http%', false])
            ->all();

        $queue = Craft::$app->getQueue();

        foreach ($plugins as $plugin) {
            $changelogPath = basename($plugin['changelogPath']);
            if (strpos($changelogPath, '#') !== false || strpos($changelogPath, '.md') === false) {
                $changelogPath = null;
            }

            $this->update('craftcom_plugins', ['changelogPath' => $changelogPath], ['id' => $plugin['id']], [], false);

            if ($changelogPath) {
                $queue->push(new UpdatePackage([
                    'name' => $plugin['packageName'],
                    'force' => true
                ]));
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171203_174013_fix_changelog_paths cannot be reverted.\n";
        return false;
    }
}
