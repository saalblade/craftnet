<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\elements\User;
use craft\helpers\Console;
use craftnet\cms\CmsLicense;
use craftnet\Module;

/**
 * m180912_040313_reactivate_all_cmslicenses migration.
 */
class m180912_040313_reactivate_all_cmslicenses extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $query = (new Query())
            ->select(['key'])
            ->from(['craftnet_inactivecmslicenses']);

        $cmsLicenseManager = Module::getInstance()->getCmsLicenseManager();

        foreach ($query->each() as $result) {
            Console::stdout('    > Reactivating ' . substr($result['key'], 0, 10) . ' ... ');
            $cmsLicenseManager->getLicenseByKey($result['key']);
            Console::output(Console::ansiFormat('done', [Console::FG_GREEN]));
        }

        $totalRemaining = $query->count();
        if ($totalRemaining != 0) {
            Console::error(Console::ansiFormat($totalRemaining . ' remaining rows in craftnet_inactivecmslicenses. Expected 0.', [Console::FG_RED]));
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180912_040313_reactivate_all_cmslicenses cannot be reverted.\n";
        return false;
    }
}
