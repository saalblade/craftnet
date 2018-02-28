<?php

namespace craftcom\plugins;

use Craft;
use craft\db\Query;
use craft\helpers\Db;
use craftcom\errors\LicenseNotFoundException;
use yii\base\Component;
use yii\base\Exception;

class PluginLicenseManager extends Component
{
    /**
     * Returns a license by its key.
     *
     * @param string $key
     * @return PluginLicense
     * @throws LicenseNotFoundException if $key is missing
     */
    public function getLicenseByKey(string $key): PluginLicense
    {
        $result = $this->_createLicenseQuery()
            ->where(['key' => $key])
            ->one();

        if ($result === null) {
            throw new LicenseNotFoundException($key);
        }

        return new PluginLicense($result);
    }

    /**
     * Saves a license.
     *
     * @param PluginLicense $license
     * @param bool $runValidation
     * @return bool if the license validated and was saved
     * @throws Exception if the license validated but didn't save
     */
    public function saveLicense(PluginLicense $license, bool $runValidation = true): bool
    {
        if ($runValidation && !$license->validate()) {
            Craft::info('License not saved due to validation error.', __METHOD__);
            return false;
        }

        $data = [
            'pluginId' => $license->pluginId,
            'editionId' => $license->editionId,
            'cmsLicenseId' => $license->cmsLicenseId,
            'expirable' => $license->expirable,
            'expired' => $license->expired,
            'email' => $license->email,
            'key' => $license->key,
            'notes' => $license->notes,
            'lastVersion' => $license->lastVersion,
            'lastAllowedVersion' => $license->lastAllowedVersion,
            'lastActivityOn' => Db::prepareDateForDb($license->lastActivityOn),
            'lastRenewedOn' => Db::prepareDateForDb($license->lastRenewedOn),
            'expiresOn' => Db::prepareDateForDb($license->expiresOn),
        ];

        if (!$license->id) {
            $success = (bool)Craft::$app->getDb()->createCommand()
                ->insert('craftcom_pluginlicenses', $data)
                ->execute();

            // set the ID on the model
            $license->id = Craft::$app->getDb()->getLastInsertID('craftcom_pluginlicenses');
        } else {
            $success = (bool)Craft::$app->getDb()->createCommand()
                ->update('craftcom_pluginlicenses', $data, ['id' => $license->id])
                ->execute();
        }

        if (!$success) {
            throw new Exception('License validated but didn’t save.');
        }

        return true;
    }

    /**
     * Upgrades a license.
     *
     * @param string $key
     * @param PluginEdition $edition
     * @param int|null $lineItemId
     * @throws LicenseNotFoundException if $key is missing
     */
    public function upgradeLicense(string $key, PluginEdition $edition, int $lineItemId = null)
    {
        $license = $this->getLicenseByKey($key);
        $license->editionId = $edition->id;
        $license->expired = false;

        // If this was placed before April 4, set the license to non-expirable
        if (time() < 1522800000) {
            $license->expirable = false;
        }

        $this->saveLicense($license, false);

        // Save the line item relation if we have an ID
        if ($lineItemId !== null) {
            Craft::$app->getDb()->createCommand()
                ->insert('craftcom_pluginlicenses_lineitems', [
                    'licenseId' => $license->id,
                    'lineItemId' => $lineItemId,
                ], false)
                ->execute();
        }
    }

    /**
     * @return Query
     */
    private function _createLicenseQuery(): Query
    {
        return (new Query())
            ->select([
                'id',
                'pluginId',
                'editionId',
                'cmsLicenseId',
                'expirable',
                'expired',
                'email',
                'key',
                'notes',
                'lastVersion',
                'lastAllowedVersion',
                'lastActivityOn',
                'lastRenewedOn',
                'expiresOn',
                'dateCreated',
                'dateUpdated',
                'uid',
            ])
            ->from(['craftcom_pluginlicenses']);
    }
}
