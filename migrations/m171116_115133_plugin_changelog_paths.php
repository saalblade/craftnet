<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m171116_115133_plugin_changelog_paths migration.
 */
class m171116_115133_plugin_changelog_paths extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->renameColumn('craftcom_plugins', 'changelogUrl', 'changelogPath');

        // Normalize empty URLs to null
        $this->update('craftcom_plugins', [
            'changelogPath' => null,
        ], [
            'changelogPath' => ''
        ], [], false);

        // All of the plugins that have changelogs currently set them to /CHANGELOG.md
        $this->update('craftcom_plugins', [
            'changelogPath' => 'CHANGELOG.md',
        ], ['not', ['changelogPath' => null]], [], false);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171116_115133_plugin_changelog_paths cannot be reverted.\n";
        return false;
    }
}
