<?php

namespace craftcom\cms;

use Craft;
use craft\db\Query;
use craft\helpers\Db;
use craftcom\errors\LicenseNotFoundException;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidArgumentException;

class CmsLicenseManager extends Component
{
    const EDITION_PERSONAL = 'personal';
    const EDITION_CLIENT = 'client';
    const EDITION_PRO = 'pro';

    /**
     * Normalizes a license key by trimming whitespace and removing newlines.
     *
     * @param string $key
     * @return string
     * @throws InvalidArgumentException if $key is invalid
     */
    public function normalizeKey(string $key): string
    {
        $normalized = trim(preg_replace('/[\r\n]+/', '', $key));
        if (strlen($normalized) !== 250) {
            throw new InvalidArgumentException('Invalid license key: '.$key);
        }
        return $normalized;
    }

    /**
     * Returns a license by its key.
     *
     * @param string $key
     * @return CmsLicense
     * @throws LicenseNotFoundException if $key is missing
     */
    public function getLicenseByKey(string $key): CmsLicense
    {
        try {
            $key = $this->normalizeKey($key);
        } catch (InvalidArgumentException $e) {
            throw new LicenseNotFoundException($key, $e->getMessage(), 0, $e);
        }

        $result = $this->_createLicenseQuery()
            ->where(['key' => $key])
            ->one();

        if ($result === null) {
            throw new LicenseNotFoundException($key);
        }

        return new CmsLicense($result);
    }

    /**
     * Saves a license.
     *
     * @param CmsLicense $license
     * @param bool $runValidation
     * @return bool if the license validated and was saved
     * @throws Exception if the license validated but didn't save
     */
    public function saveLicense(CmsLicense $license, bool $runValidation = true): bool
    {
        if ($runValidation && !$license->validate()) {
            Craft::info('License not saved due to validation error.', __METHOD__);
            return false;
        }

        if (!$license->editionId) {
            $license->editionId = (int)CmsEdition::find()
                ->select('elements.id')
                ->handle($license->edition)
                ->scalar();
        }

        $data = [
            'editionId' => $license->editionId,
            'expirable' => $license->expirable,
            'expired' => $license->expired,
            'edition' => $license->edition,
            'email' => $license->email,
            'domain' => $license->domain,
            'key' => $license->key,
            'notes' => $license->notes,
            'lastEdition' => $license->lastEdition,
            'lastVersion' => $license->lastVersion,
            'lastAllowedVersion' => $license->lastAllowedVersion,
            'lastActivityOn' => Db::prepareDateForDb($license->lastActivityOn),
            'lastRenewedOn' => Db::prepareDateForDb($license->lastRenewedOn),
            'expiresOn' => Db::prepareDateForDb($license->expiresOn),
        ];

        if (!$license->id) {
            $success = (bool)Craft::$app->getDb()->createCommand()
                ->insert('craftcom_cmslicenses', $data)
                ->execute();

            // set the ID an UID on the model
            $license->id = Craft::$app->getDb()->getLastInsertID('craftcom_cmslicenses');
        } else {
            $success = (bool)Craft::$app->getDb()->createCommand()
                ->update('craftcom_cmslicenses', $data, ['id' => $license->id])
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
     * @param CmsEdition $edition
     * @param int|null $lineItemId
     * @throws LicenseNotFoundException if $key is missing
     */
    public function upgradeLicense(string $key, CmsEdition $edition, int $lineItemId = null)
    {
        $license = $this->getLicenseByKey($key);
        $license->edition = $edition->handle;
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
                ->insert('craftcom_cmslicenses_lineitems', [
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
                'editionId',
                'expirable',
                'expired',
                'edition',
                'email',
                'domain',
                'key',
                'notes',
                'lastEdition',
                'lastVersion',
                'lastAllowedVersion',
                'lastActivityOn',
                'lastRenewedOn',
                'expiresOn',
                'dateCreated',
                'dateUpdated',
                'uid',
            ])
            ->from(['craftcom_cmslicenses']);
    }
}
