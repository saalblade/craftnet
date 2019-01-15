<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m181123_183643_plugin_editions migration.
 */
class m181123_183643_plugin_editions extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Add the features column
        $this->addColumn('craftnet_plugineditions', 'features', $this->text());

        // Update renewal prices
        // (due to a bug these were always getting set to the main price)
        $sql = <<<SQL
update craftnet_plugineditions
set "renewalPrice" = coalesce(subquery."renewalPrice", 0)
from (
  select id, "renewalPrice"
  from craftnet_plugins
) as subquery
where craftnet_plugineditions."pluginId" = subquery.id
SQL;

        $this->execute($sql);

        // Delete the price columns from the main plugin table
        $this->dropColumn('craftnet_plugins', 'price');
        $this->dropColumn('craftnet_plugins', 'renewalPrice');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m181123_183643_plugin_editions cannot be reverted.\n";
        return false;
    }
}
