<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m180305_110134_logs migration.
 */
class m180305_110134_logs extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute('drop schema if exists [[apilog]] cascade');
        $this->execute('create schema [[apilog]]');

        $this->createTable('apilog.requests', [
            'id' => $this->bigPrimaryKey(),
            'verb' => $this->string()->notNull(),
            'uri' => $this->string()->notNull(),
            'ip' => $this->string()->notNull(),
            'action' => $this->string()->notNull(),
            'body' => $this->text()->null(),
            'system' => $this->text()->null(),
            'platform' => $this->text()->null(),
            'host' => $this->string()->null(),
            'userEmail' => $this->string()->null(),
            'userIp' => $this->string()->null(),
            'timestamp' => $this->dateTime()->notNull(),
            'responseCode' => $this->smallInteger()->notNull(),
        ]);

        $this->createTable('apilog.request_cmslicenses', [
            'requestId' => $this->bigInteger()->notNull(),
            'licenseId' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey('request_cmslicenses_requestId_fk', 'apilog.request_cmslicenses', ['requestId'], 'apilog.requests', ['id'], 'CASCADE');
        $this->addForeignKey('request_cmslicenses_licenseId_fk', 'apilog.request_cmslicenses', ['licenseId'], 'public.craftcom_cmslicenses', ['id'], 'CASCADE');

        $this->createTable('apilog.request_pluginlicenses', [
            'requestId' => $this->bigInteger()->notNull(),
            'licenseId' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey('request_pluginlicenses_requestId_fk', 'apilog.request_pluginlicenses', ['requestId'], 'apilog.requests', ['id'], 'CASCADE');
        $this->addForeignKey('request_pluginlicenses_licenseId_fk', 'apilog.request_pluginlicenses', ['licenseId'], 'public.craftcom_pluginlicenses', ['id'], 'CASCADE');

        $this->createTable('apilog.request_errors', [
            'requestId' => $this->bigInteger()->notNull(),
            'type' => $this->string(),
            'message' => $this->text()->notNull(),
            'stackTrace' => $this->text(),
        ]);
        $this->addForeignKey('request_errors_requestId_fk', 'apilog.request_errors', ['requestId'], 'apilog.requests', ['id'], 'CASCADE');

        $this->createTable('apilog.logs', [
            'id' => $this->bigPrimaryKey(),
            'requestId' => $this->bigInteger(),
            'level' => $this->integer(),
            'category' => $this->string(),
            'timestamp' => $this->double(),
            'prefix' => $this->text(),
            'message' => $this->text(),
        ]);
        $this->createIndex('logs_level_idx', 'apilog.logs', 'level');
        $this->createIndex('logs_category_idx', 'apilog.logs', 'category');
        $this->addForeignKey('logs_requestId_fk', 'apilog.request_pluginlicenses', ['requestId'], 'apilog.requests', ['id'], 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180305_110134_logs cannot be reverted.\n";
        return false;
    }
}
