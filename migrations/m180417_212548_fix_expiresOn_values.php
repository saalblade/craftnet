<?php

namespace craft\contentmigrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craftnet\cms\CmsLicenseManager;

/**
 * m180417_212548_fix_expiresOn_values migration.
 */
class m180417_212548_fix_expiresOn_values extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $cmsLicenses = (new Query())
            ->select(['l.id', 'l.key', 'li.dateCreated'])
            ->from(['craftnet_cmslicenses l'])
            ->innerJoin('craftnet_cmslicenses_lineitems l_li', '[[l_li.licenseId]] = [[l.id]]')
            ->innerJoin('commerce_lineitems li', '[[li.id]] = [[l_li.lineItemId]]')
            ->where(['l.editionHandle' => CmsLicenseManager::EDITION_PRO, 'l.expirable' => true, 'expiresOn' => null])
            ->all();

        foreach ($cmsLicenses as $license) {
            $shortKey = substr($license['key'], 0, 10);
            $expiresOn = DateTimeHelper::toDateTime($license['dateCreated'])->modify('+1 year');
            echo "    > setting expiry date for {$shortKey} ...\n  ";
            $this->update('craftnet_cmslicenses', [
                'expiresOn' => Db::prepareDateForDb($expiresOn),
            ], [
                'id' => $license['id'],
            ], [], false);
        }

        $pluginLicenses = (new Query())
            ->select(['id', 'key', 'dateCreated'])
            ->from(['craftnet_pluginlicenses'])
            ->where(['expirable' => true, 'expiresOn' => null])
            ->all();

        foreach ($pluginLicenses as $license) {
            $expiresOn = DateTimeHelper::toDateTime($license['dateCreated'])->modify('+1 year');
            echo "    > setting expiry date for {$license['key']} ...\n  ";
            $this->update('craftnet_pluginlicenses', [
                'expiresOn' => Db::prepareDateForDb($expiresOn),
            ], [
                'id' => $license['id'],
            ], [], false);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180417_212548_fix_expiresOn_values cannot be reverted.\n";
        return false;
    }
}
