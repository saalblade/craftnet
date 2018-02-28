<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craftcom\cms\CmsEdition;
use craftcom\cms\CmsRenewal;
use craftcom\plugins\PluginEdition;
use craftcom\plugins\PluginRenewal;
use yii\console\Exception;

/**
 * m180227_232204_create_license_tables migration.
 */
class m180227_232204_create_license_tables extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->_createCmsTables();
        $this->_createPluginTables();
        $this->_createCmsEditions();
        $this->_createPluginEditions();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180227_232204_create_license_tables cannot be reverted.\n";
        return false;
    }

    private function _createCmsTables()
    {
        // fix plugins table ---------------------------------------------------

        $this->alterColumn('craftcom_plugins', 'price',  $this->decimal(14, 4)->unsigned());
        $this->alterColumn('craftcom_plugins', 'renewalPrice',  $this->decimal(14, 4)->unsigned());

        // cmseditions ---------------------------------------------------------

        $this->createTable('craftcom_cmseditions', [
            'id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'price' => $this->decimal(14, 4)->unsigned()->notNull(),
            'renewalPrice' => $this->decimal(14, 4)->unsigned()->notNull(),
            'PRIMARY KEY(id)',
        ]);

        $this->createIndex(null, 'craftcom_cmseditions', ['name'], true);
        $this->createIndex(null, 'craftcom_cmseditions', ['handle'], true);
        $this->createIndex(null, 'craftcom_cmseditions', ['price']);

        $this->addForeignKey(null, 'craftcom_cmseditions', ['id'], 'elements', ['id'], 'CASCADE');

        // cmsrenewals ---------------------------------------------------------

        $this->createTable('craftcom_cmsrenewals', [
            'id' => $this->integer()->notNull(),
            'editionId' => $this->integer()->notNull(),
            'price' => $this->decimal(14, 4)->unsigned()->notNull(),
            'PRIMARY KEY(id)',
        ]);

        $this->addForeignKey(null, 'craftcom_cmsrenewals', ['id'], 'elements', ['id'], 'CASCADE');
        $this->addForeignKey(null, 'craftcom_cmsrenewals', ['editionId'], 'craftcom_cmseditions', ['id'], 'CASCADE');

        // cmslicenses ---------------------------------------------------------

        $this->createTable('craftcom_cmslicenses', [
            'id' => $this->primaryKey(),
            'editionId' => $this->integer()->notNull(),
            'expirable' => $this->boolean()->notNull(),
            'expired' => $this->boolean()->notNull()->defaultValue(false),
            'edition' => $this->string()->notNull(),
            'email' => $this->string()->notNull(),
            'hostname' => $this->string()->null(),
            'key' => $this->string(250)->notNull(),
            'notes' => $this->text()->null(),
            'lastEdition' => $this->smallInteger()->null(),
            'lastVersion' => $this->string()->null(),
            'lastAllowedVersion' => $this->string()->null(),
            'lastActivityOn' => $this->dateTime()->null(),
            'lastRenewedOn' => $this->dateTime()->null(),
            'expiresOn' => $this->dateTime()->null(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->addForeignKey(null, 'craftcom_cmslicenses', ['editionId'], 'craftcom_cmseditions', ['id']);

        // cmslicenses_lineitems -----------------------------------------------

        $this->createTable('craftcom_cmslicenses_lineitems', [
            'licenseId' => $this->integer()->notNull(),
            'lineItemId' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(null, 'craftcom_cmslicenses_lineitems', ['licenseId'], 'craftcom_cmslicenses', ['id'], 'CASCADE');
        $this->addForeignKey(null, 'craftcom_cmslicenses_lineitems', ['lineItemId'], 'commerce_lineitems', ['id'], 'CASCADE');
    }

    private function _createPluginTables()
    {
        // plugineditions ------------------------------------------------------

        $this->createTable('craftcom_plugineditions', [
            'id' => $this->integer()->notNull(),
            'pluginId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'price' => $this->decimal(14, 4)->unsigned()->notNull(),
            'renewalPrice' => $this->decimal(14, 4)->unsigned()->notNull(),
            'PRIMARY KEY(id)',
        ]);

        $this->createIndex(null, 'craftcom_plugineditions', ['pluginId', 'name'], true);
        $this->createIndex(null, 'craftcom_plugineditions', ['pluginId', 'handle'], true);
        $this->createIndex(null, 'craftcom_plugineditions', ['pluginId', 'price']);

        $this->addForeignKey(null, 'craftcom_plugineditions', ['id'], 'elements', ['id'], 'CASCADE');
        $this->addForeignKey(null, 'craftcom_plugineditions', ['pluginId'], 'craftcom_plugins', ['id'], 'CASCADE');

        // cmsrenewals ---------------------------------------------------------

        $this->createTable('craftcom_pluginrenewals', [
            'id' => $this->integer()->notNull(),
            'pluginId' => $this->integer()->notNull(),
            'editionId' => $this->integer()->notNull(),
            'price' => $this->decimal(14, 4)->unsigned()->notNull(),
            'PRIMARY KEY(id)',
        ]);

        $this->addForeignKey(null, 'craftcom_pluginrenewals', ['id'], 'elements', ['id'], 'CASCADE');
        $this->addForeignKey(null, 'craftcom_pluginrenewals', ['pluginId'], 'craftcom_plugins', ['id'], 'CASCADE');
        $this->addForeignKey(null, 'craftcom_pluginrenewals', ['editionId'], 'craftcom_plugineditions', ['id'], 'CASCADE');

        // pluginlicenses ------------------------------------------------------

        $this->createTable('craftcom_pluginlicenses', [
            'id' => $this->primaryKey(),
            'pluginId' => $this->integer()->notNull(),
            'editionId' => $this->integer()->notNull(),
            'cmsLicenseId' => $this->integer()->null(),
            'expirable' => $this->boolean()->notNull(),
            'expired' => $this->boolean()->notNull()->defaultValue(false),
            'email' => $this->string()->notNull(),
            'key' => $this->string(24)->notNull(),
            'notes' => $this->text()->null(),
            'lastVersion' => $this->string()->null(),
            'lastAllowedVersion' => $this->string()->null(),
            'lastActivityOn' => $this->dateTime()->null(),
            'lastRenewedOn' => $this->dateTime()->null(),
            'expiresOn' => $this->dateTime()->null(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->addForeignKey(null, 'craftcom_pluginlicenses', ['pluginId'], 'craftcom_plugins', ['id']);
        $this->addForeignKey(null, 'craftcom_pluginlicenses', ['editionId'], 'craftcom_plugineditions', ['id']);
        $this->addForeignKey(null, 'craftcom_pluginlicenses', ['cmsLicenseId'], 'craftcom_cmslicenses', ['id'], 'SET NULL');

        // pluginlicenses_lineitems --------------------------------------------

        $this->createTable('craftcom_pluginlicenses_lineitems', [
            'licenseId' => $this->integer()->notNull(),
            'lineItemId' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(null, 'craftcom_pluginlicenses_lineitems', ['licenseId'], 'craftcom_pluginlicenses', ['id'], 'CASCADE');
        $this->addForeignKey(null, 'craftcom_pluginlicenses_lineitems', ['lineItemId'], 'commerce_lineitems', ['id'], 'CASCADE');
    }

    private function _createCmsEditions()
    {
        $elementsService = Craft::$app->getElements();

        /** @var CmsEdition[] $editions */
        $editions = [
            new CmsEdition([
                'name' => 'Personal',
                'handle' => 'personal',
                'price' => 0,
                'renewalPrice' => 0,
            ]),
            new CmsEdition([
                'name' => 'Client',
                'handle' => 'client',
                'price' => 199,
                'renewalPrice' => 39,
            ]),
            new CmsEdition([
                'name' => 'Pro',
                'handle' => 'pro',
                'price' => 299,
                'renewalPrice' => 59,
            ]),
        ];

        foreach ($editions as $edition) {
            // Save the edition
            if (!$elementsService->saveElement($edition)) {
                throw new Exception("Couldn't save Craft {$edition->name} edition: ".implode(', ', $edition->getFirstErrors()));
            }

            // Save the renewal
            $renewal = new CmsRenewal([
                'editionId' => $edition->id,
                'price' => $edition->renewalPrice,
            ]);

            if (!$elementsService->saveElement($renewal)) {
                throw new Exception("Couldn't save Craft {$edition->name} renewal: ".implode(', ', $renewal->getFirstErrors()));
            }
        }
    }

    private function _createPluginEditions()
    {
        $elementsService = Craft::$app->getElements();

        $plugins = (new Query())
            ->select(['id', 'name', 'price', 'renewalPrice'])
            ->from('craftcom_plugins')
            ->all();

        foreach ($plugins as $plugin) {
            // Save the edition
            $edition = new PluginEdition([
                'pluginId' => $plugin['id'],
                'name' => 'Standard',
                'handle' => 'standard',
                'price' => $plugin['price'] ?? 0,
                'renewalPrice' => $plugin['renewalPrice'] ?? 0,
            ]);

            if (!$elementsService->saveElement($edition)) {
                throw new Exception("Couldn't save {$plugin['name']} edition: ".implode(', ', $edition->getFirstErrors()));
            }

            // Save the renewal
            $renewal = new PluginRenewal([
                'pluginId' => $plugin['id'],
                'editionId' => $edition->id,
                'price' => $edition->renewalPrice,
            ]);

            if (!$elementsService->saveElement($renewal)) {
                throw new Exception("Couldn't save {$plugin['name']} renewal: ".implode(', ', $renewal->getFirstErrors()));
            }
        }
    }
}
