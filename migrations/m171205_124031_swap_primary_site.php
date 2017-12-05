<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m171205_124031_swap_primary_site migration.
 */
class m171205_124031_swap_primary_site extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // API => Craft ID (new)
        $this->update('sites', [
            'handle' => 'craftId_new',
            'name' => 'Craft ID (new)',
            'baseUrl' => 'https://id.craftcms.com/',
        ], ['id' => 1]);

        // Craft ID => API
        $this->update('sites', [
            'handle' => 'api',
            'name' => 'API',
            'baseUrl' => 'https://api.craftcms.com/',
        ], ['id' => 3]);

        // Craft ID (new) => Craft ID
        $this->update('sites', [
            'handle' => 'craftId',
            'name' => 'Craft ID',
        ], ['id' => 1]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171205_124031_swap_primary_site cannot be reverted.\n";
        return false;
    }
}
