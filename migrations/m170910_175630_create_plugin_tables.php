<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m170910_175630_create_plugin_tables migration.
 */
class m170910_175630_create_plugin_tables extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('craftcom_plugins', [
            'id' => $this->integer()->notNull(),
            'developerId' => $this->integer()->notNull(),
            'packageId' => $this->integer()->notNull(),
            'iconId' => $this->integer(),
            'packageName' => $this->string()->notNull(),
            'repository' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'price' => $this->integer(),
            'renewalPrice' => $this->integer(),
            'license' => $this->string()->notNull(),
            'shortDescription' => $this->text(),
            'longDescription' => $this->text(),
            'documentationUrl' => $this->string(),
            'changelogUrl' => $this->string(),
            'latestVersion' => $this->string(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);

        $this->createTable('craftcom_plugincategories', [
            'id' => $this->primaryKey(),
            'pluginId' => $this->integer()->notNull(),
            'categoryId' => $this->integer()->notNull(),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('craftcom_pluginscreenshots', [
            'id' => $this->primaryKey(),
            'pluginId' => $this->integer()->notNull(),
            'assetId' => $this->integer()->notNull(),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, 'craftcom_plugins', ['name'], true);
        $this->createIndex(null, 'craftcom_plugins', ['handle'], true);
        $this->createIndex(null, 'craftcom_plugincategories', ['pluginId', 'categoryId'], true);
        $this->createIndex(null, 'craftcom_pluginscreenshots', ['pluginId', 'assetId'], true);

        $this->addForeignKey(null, 'craftcom_plugins', ['id'], 'elements', ['id'], 'CASCADE');
        $this->addForeignKey(null, 'craftcom_plugins', ['developerId'], 'users', ['id'], 'CASCADE');
        $this->addForeignKey(null, 'craftcom_plugins', ['packageId'], 'craftcom_packages', ['id']);
        $this->addForeignKey(null, 'craftcom_plugins', ['iconId'], 'assets', ['id'], 'SET NULL');
        $this->addForeignKey(null, 'craftcom_plugincategories', ['pluginId'], 'craftcom_plugins', ['id'], 'CASCADE');
        $this->addForeignKey(null, 'craftcom_plugincategories', ['categoryId'], 'categories', ['id'], 'CASCADE');
        $this->addForeignKey(null, 'craftcom_pluginscreenshots', ['pluginId'], 'craftcom_plugins', ['id'], 'CASCADE');
        $this->addForeignKey(null, 'craftcom_pluginscreenshots', ['assetId'], 'assets', ['id'], 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m170910_175630_create_plugin_tables cannot be reverted.\n";
        return false;
    }
}
