<?php

namespace craft\contentmigrations;

use Composer\Semver\VersionParser;
use Craft;
use craft\db\Migration;
use craft\db\Query;

/**
 * m171115_190400_package_version_stabilities migration.
 */
class m171115_190400_package_version_stabilities extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftcom_packageversions', 'stability', $this->string());

        $versions = (new Query())
            ->select(['id', 'version'])
            ->from(['craftcom_packageversions'])
            ->all($this->db);

        foreach ($versions as $version) {
            $stability = VersionParser::parseStability($version['version']);
            $this->update('craftcom_packageversions', ['stability' => $stability], ['id' => $version['id']]);
        }

        // Manually construct the SQL for Postgres
        // (see https://github.com/yiisoft/yii2/issues/12077)
        $this->execute('alter table {{craftcom_packageversions}} alter column [[stability]] set not null');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171115_190400_package_version_stabilities cannot be reverted.\n";
        return false;
    }
}
