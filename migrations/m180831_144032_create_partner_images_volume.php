<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m180831_144032_create_partner_images_volume migration.
 */
class m180831_144032_create_partner_images_volume extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $volumes = Craft::$app->getVolumes();
        $exisitngVolume = $volumes->getVolumeByHandle('partnerImages');
        $id = $exisitngVolume ? $exisitngVolume->id : null;

        if ($id) {
            echo "Updating existing Partner Images volume\n";
        }

        $volume = $volumes->createVolume([
            'id' => $id,
            'type' => 'craft\awss3\Volume',
            'name' => 'Partner Images',
            'handle' => 'partnerImages',
            'hasUrls' => true,
            'url' => 'http://partners.craftcms.s3.amazonaws.com/',
            'settings' => [
                'keyId' => getenv('AWS_ACCESS_KEY_ID'),
                'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
                'bucket' => 'partners.craftcms',
                'region' => 'us-east-1',
                'subfolder' => 'images',
                'expires' => '',
                'cfDistributionId' => '',
            ]
        ]);

        $success = $volumes->saveVolume($volume);

        if (!$success) {
            echo "Can't save volume: Partner Images\n";
        }

        return $success;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180831_144032_create_partner_images_volume cannot be undone but reversion is allowed.\n";
        return true;
    }
}
