<?php

namespace craftcom\cms;

use Craft;
use craft\commerce\elements\Order;
use craft\db\Query;
use craft\elements\User;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craft\helpers\Json;
use craftcom\errors\LicenseNotFoundException;
use craftcom\Module;
use craftcom\plugins\Plugin;
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
     * @see normalizeDomain()
     */
    public $devDomains = [];

    /**
     * @var array TLDs that we treat as private, because they are only used for dev/testing/staging purposes
     * @see normalizeDomain()
     */
    public $devTlds = [];

    /**
     * @var array Words that can be found in the subdomain that will cause the domain to be treated as private
     * @see normalizeDomain()
     */
    public $devSubdomainWords = [];

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
        $subdomain = $result->getSubdomain();
        if ($subdomain && array_intersect(preg_split('/\b/', $subdomain), $this->devSubdomainWords)) {
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
    public function getLicensesByOwner(int $ownerId): array
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
     * Returns a license by its ID.
     *
     * @param int $id
     * @return CmsLicense
     * @throws LicenseNotFoundException if $id is missing
     */
    public function getLicenseById(int $id): CmsLicense
    {
        $result = $this->_createLicenseQuery()
            ->where(['id' => $id])
            ->one();

        if (!$result) {
            throw new LicenseNotFoundException($id);
        }

        return new CmsLicense($result);
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
            // try the inactive licenses table
            $data = (new Query())
                ->select(['data'])
                ->from(['craftcom_inactivecmslicenses'])
                ->where(['key' => $key])
                ->scalar();

            if ($data === false) {
                throw new LicenseNotFoundException($key);
            }

            $license = new CmsLicense(Json::decode($data));
            $this->saveLicense($license, false);

            Craft::$app->getDb()->createCommand()
                ->delete('craftcom_inactivecmslicenses', [
                    'key' => $key
                ])
                ->execute();

            return $license;
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

            if ($license->editionId === false) {
                throw new Exception("Invalid Craft edition: {$license->edition}");
            }
        }

        $data = [
            'editionId' => $license->editionId,
            'ownerId' => $license->ownerId,
            'expirable' => $license->expirable,
            'expired' => $license->expired,
            'autoRenew' => $license->autoRenew,
            'edition' => $license->edition,
            'email' => $license->email,
            'domain' => $license->domain,
            'key' => $license->key,
            'notes' => $license->notes,
            'privateNotes' => $license->privateNotes,
            'lastEdition' => $license->lastEdition,
            'lastVersion' => $license->lastVersion,
            'lastAllowedVersion' => $license->lastAllowedVersion,
            'lastActivityOn' => Db::prepareDateForDb($license->lastActivityOn),
            'lastRenewedOn' => Db::prepareDateForDb($license->lastRenewedOn),
            'expiresOn' => Db::prepareDateForDb($license->expiresOn),
            'dateCreated' => Db::prepareDateForDb($license->dateCreated),
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
     * Finds unclaimed licenses that are associated with orders placed by the given user's email,
     * and and assigns them to the user.
     *
     * @param User $user
     */
    public function claimLicenses(User $user)
    {
        $orderIds = Order::find()
            ->email($user->email)
            ->isCompleted(true)
            ->ids();

        if (!empty($orderIds)) {
            $cmsLicenseIds = (new Query())
                ->select(['l.id'])
                ->from(['craftcom_cmslicenses l'])
                ->innerJoin('craftcom_cmslicenses_lineitems l_li', '[[l_li.licenseId]] = [[l.id]]')
                ->innerJoin('commerce_lineitems li', '[[li.id]] = [[l_li.lineItemId]]')
                ->where(['l.ownerId' => null, 'li.orderId' => $orderIds])
                ->column();

            if (!empty($cmsLicenseIds)) {
                Craft::$app->getDb()->createCommand()
                    ->update('craftcom_cmslicenses', [
                        'ownerId' => $user->id,
                    ], ['id' => $cmsLicenseIds])
                    ->execute();
            }
        }
    }

    /**
     * Returns licenses by owner as an array.
     *
     * @param User $owner
     *
     * @return array
     */
    public function getLicensesArrayByOwner(User $owner)
    {
        $results = Module::getInstance()->getCmsLicenseManager()->getLicensesByOwner($owner->id);

        $licenses = [];

        foreach ($results as $result) {
            $license = $result->getAttributes(['id', 'key', 'edition', 'domain', 'notes', 'email', 'dateCreated']);

            $pluginLicensesResults = Module::getInstance()->getPluginLicenseManager()->getLicensesByCmsLicenseId($result->id);

            $pluginLicenses = [];

            foreach ($pluginLicensesResults as $key => $pluginLicensesResult) {
                if ($pluginLicensesResult->ownerId === $owner->id) {
                    $pluginLicense = $pluginLicensesResult->getAttributes(['id', 'key']);
                } else {
                    $pluginLicense = [
                        'shortKey' => $pluginLicensesResult->getShortKey(),
                    ];
                }

                $plugin = null;

                if ($pluginLicensesResult->pluginId) {
                    $pluginResult = Plugin::find()->id($pluginLicensesResult->pluginId)->status(null)->one();
                    $plugin = $pluginResult->getAttributes(['name']);
                }

                $pluginLicense['plugin'] = $plugin;

                $pluginLicenses[] = $pluginLicense;
            }

            $license['pluginLicenses'] = $pluginLicenses;
            $licenses[] = $license;
        }

        return $licenses;
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
                'autoRenew',
                'edition',
                'email',
                'domain',
                'key',
                'notes',
                'privateNotes',
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
