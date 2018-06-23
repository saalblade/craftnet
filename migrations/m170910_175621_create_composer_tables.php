<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m170910_175621_create_composer_tables migration.
 */
class m170910_175621_create_composer_tables extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('craftcom_packages', [
            'id' => $this->primaryKey(),
            'name' => $this->string(191)->notNull(),
            'type' => $this->string()->notNull(),
            'repository' => $this->string(),
            'managed' => $this->boolean()->defaultValue(false),
            'latestVersion' => $this->string(),
            'abandoned' => $this->boolean()->defaultValue(false),
            'replacementPackage' => $this->string(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('craftcom_packageversions', [
            'id' => $this->primaryKey(),
            'packageId' => $this->integer()->notNull(),
            'sha' => $this->string()->notNull(),
            'description' => $this->text(),
            'version' => $this->string()->notNull(),
            'normalizedVersion' => $this->string(191)->notNull(),
            'type' => $this->string(),
            'keywords' => $this->text(),
            'homepage' => $this->text(),
            'time' => $this->dateTime(),
            'license' => $this->text(),
            'authors' => $this->text(),
            'support' => $this->text(),
            'conflict' => $this->text(),
            'replace' => $this->text(),
            'provide' => $this->text(),
            'suggest' => $this->text(),
            'autoload' => $this->text(),
            'includePaths' => $this->text(),
            'targetDir' => $this->string(),
            'extra' => $this->text(),
            'binaries' => $this->text(),
            'source' => $this->text(),
            'dist' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('craftcom_packagedeps', [
            'id' => $this->primaryKey(),
            'packageId' => $this->integer()->notNull(),
            'versionId' => $this->integer()->notNull(),
            'name' => $this->string(191)->notNull(),
            'constraints' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, 'craftcom_packages', ['name'], true);
        $this->createIndex(null, 'craftcom_packages', ['type']);
        $this->createIndex(null, 'craftcom_packageversions', ['packageId', 'version'], true);

        $this->addForeignKey(null, 'craftcom_packageversions', ['packageId'], 'craftcom_packages', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, 'craftcom_packagedeps', ['packageId'], 'craftcom_packages', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, 'craftcom_packagedeps', ['versionId'], 'craftcom_packageversions', ['id'], 'CASCADE', null);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m170910_175621_create_composer_tables cannot be reverted.\n";
        return false;
    }
}
