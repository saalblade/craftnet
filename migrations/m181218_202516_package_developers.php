<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m181218_202516_package_developers migration.
 */
class m181218_202516_package_developers extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftnet_packages', 'developerId', $this->integer());
        $this->addForeignKey(null, 'craftnet_packages', ['developerId'], 'users', ['id'], 'SET NULL');

        // Populate the new developerId column with plugins' developerId's
        $sql = <<<SQL
update craftnet_packages
set "developerId" = craftnet_plugins."developerId"
from craftnet_plugins
where craftnet_plugins."packageId" = craftnet_packages.id
SQL;
        $this->execute($sql);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m181218_202516_package_developers cannot be reverted.\n";
        return false;
    }
}
