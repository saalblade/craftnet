<?php

namespace craftcom\cms;

use Craft;
use craft\db\Query;
use craft\helpers\Db;
use craftcom\errors\LicenseNotFoundException;
use LayerShifter\TLDExtract\Extract;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidArgumentException;

class CmsLicenseManager extends Component
{
    const EDITION_PERSONAL = 'personal';
    const EDITION_CLIENT = 'client';
    const EDITION_PRO = 'pro';

    /**
     * @var array Domains that we treat as private, because they are only used for dev/testing/staging purposes
     */
    public $devDomains = [];

    /**
     * @var array TLDs that we treat as private, because they are only used for dev/testing/staging purposes
     */
    public $devTlds = [];

    /**
     * @var array Subdomains that we treat as private, because they are generally only used for dev/testing/staging purposes
     */
    public $devSubdomains = [];

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
     * Normalizes a public domain.
     *
     * @param string $url
     * @return string|null
     */
    public function normalizeDomain(string $url)
    {
        $result = (new Extract(null, null, Extract::MODE_ALLOW_ICANN))
            ->parse(mb_strtolower($url));

        if (($domain = $result->getRegistrableDomain()) === null) {
            return null;
        }

        // ignore if it's a dev domain
        if (
            in_array($domain, $this->devDomains, true) ||
            in_array($result->getFullHost(), $this->devDomains, true)
        ) {
            return null;
        }

        // ignore if it's a dev TLD
        if (in_array($result->getSuffix(), $this->devTlds, true)) {
            return null;
        }

        // ignore if it's a nonstandard port
        $port = parse_url($url, PHP_URL_PORT);
        if ($port && $port != 80 && $port != 443) {
            return null;
        }

        // Check if any of the subdomains sound dev-y
        $subdomains = $result->getSubdomains();
        if ($subdomains && array_intersect($subdomains, $this->devSubdomains)) {
            return null;
        }

        return $domain;
    }

    /**
     * Returns licenses owned by a user.
     *
     * @param int $ownerId
     * @return CmsLicense[]
     */
    public function getLicenseByOwner(int $ownerId): array
    {
        $results = $this->_createLicenseQuery()
            ->where(['ownerId' => $ownerId])
            ->all();

        $licenses = [];
        foreach ($results as $result) {
            $licenses[] = new CmsLicense($result);
        }
        return $licenses;
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
            'ownerId' => $license->ownerId,
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
                'ownerId',
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
