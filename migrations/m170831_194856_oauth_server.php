<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m170831_194856_oauth_server migration.
 */
class m170831_194856_oauth_server extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();
        $this->insertDefaultData();

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeIndexes();
        $this->removeTables();
        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createTables()
    {
        $this->createTable(
            '{{%oauthserver_clients}}',
            [
                'id' => $this->primaryKey(),

                'name' => $this->string(255)->notNull(),
                'identifier' => $this->string(255)->notNull(),
                'secret' => $this->string(255),
                'redirectUri' => $this->string(255),
                'redirectUriLocked' => $this->boolean()->defaultValue(false)->notNull(),

                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]
        );

        $this->createTable(
            '{{%oauthserver_access_tokens}}',
            [
                'id' => $this->primaryKey(),
                'clientId' => $this->integer()->notNull(),
                'userId' => $this->integer(),

                'identifier' => $this->string(255)->notNull(),
                'expiryDate' => $this->dateTime(),
                'userIdentifier' => $this->string(255),
                'scopes' => $this->text(),
                'isRevoked' => $this->boolean()->defaultValue(false)->notNull(),

                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]
        );

        $this->createTable(
            '{{%oauthserver_refresh_tokens}}',
            [
                'id' => $this->primaryKey(),
                'accessTokenId' => $this->integer()->notNull(),

                'identifier' => $this->string(255)->notNull(),
                'expiryDate' => $this->dateTime(),

                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]
        );

        $this->createTable(
            '{{%oauthserver_auth_codes}}',
            [
                'id' => $this->primaryKey(),
                'clientId' => $this->integer()->notNull(),
                'userId' => $this->integer(),

                'identifier' => $this->text()->notNull(),
                'expiryDate' => $this->dateTime(),
                'scopes' => $this->text(),

                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]
        );
    }

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex($this->db->getIndexName('{{%oauthserver_clients}}', 'identifier', true), '{{%oauthserver_clients}}', 'identifier', true);
    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey($this->db->getForeignKeyName('{{%oauthserver_access_tokens}}', 'userId'), '{{%oauthserver_access_tokens}}', 'userId', '{{%users}}', 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%oauthserver_access_tokens}}', 'clientId'), '{{%oauthserver_access_tokens}}', 'clientId', '{{%oauthserver_clients}}', 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%oauthserver_auth_codes}}', 'userId'), '{{%oauthserver_auth_codes}}', 'userId', '{{%users}}', 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%oauthserver_auth_codes}}', 'clientId'), '{{%oauthserver_auth_codes}}', 'clientId', '{{%oauthserver_clients}}', 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%oauthserver_refresh_tokens}}', 'accessTokenId'), '{{%oauthserver_refresh_tokens}}', 'accessTokenId', '{{%oauthserver_access_tokens}}', 'id', 'CASCADE', null);
    }

    /**
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTable('{{%oauthserver_refresh_tokens}}');
        $this->dropTable('{{%oauthserver_access_tokens}}');
        $this->dropTable('{{%oauthserver_auth_codes}}');
        $this->dropTable('{{%oauthserver_clients}}');
    }

    /**
     * Removes the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeIndexes()
    {
        $this->dropIndex($this->db->getIndexName('{{%oauthserver_clients}}', 'identifier', true), '{{%oauthserver_clients}}');
    }
}
