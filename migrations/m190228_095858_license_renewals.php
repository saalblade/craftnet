<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m190228_095858_license_renewals migration.
 */
class m190228_095858_license_renewals extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftnet_cmslicenses', 'reminded', $this->boolean()->defaultValue(false)->notNull());
        $this->addColumn('craftnet_pluginlicenses', 'reminded', $this->boolean()->defaultValue(false)->notNull());

        $this->addColumn('craftnet_cmslicenses', 'renewalPrice', $this->decimal(14, 4)->unsigned()->null());
        $this->addColumn('craftnet_pluginlicenses', 'renewalPrice', $this->decimal(14, 4)->unsigned()->null());

        $this->createIndex(null, 'craftnet_cmslicenses', ['expirable', 'reminded', 'expiresOn']);
        $this->createIndex(null, 'craftnet_pluginlicenses', ['expirable', 'reminded', 'expiresOn']);

        $this->update('craftnet_cmslicenses', ['renewalPrice' => 59], [
            'editionId' => 1259,
            'expirable' => true
        ]);

        $sql = <<<SQL
update {{craftnet_pluginlicenses}}
set [[renewalPrice]] = [[pe.renewalPrice]]
from {{craftnet_plugineditions}} pe
where [[pe.id]] = [[editionId]]
and [[expirable]] = true
SQL;

        $this->execute($sql);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190228_095858_license_renewals cannot be reverted.\n";
        return false;
    }
}
