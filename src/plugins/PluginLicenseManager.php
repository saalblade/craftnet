<?php

namespace craftcom\plugins;

use Craft;
use craft\commerce\elements\Order;
use craft\db\Query;
use craft\elements\User;
use craft\helpers\Db;
use craftcom\errors\LicenseNotFoundException;
use craftcom\Module;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidArgumentException;

class PluginLicenseManager extends Component
{
    /**
     * Normalizes a license key by trimming whitespace and removing dashes.
     *
     * @param string $key
     * @return string
     * @throws InvalidArgumentException if $key is invalid
     */
    public function normalizeKey(string $key): string
    {
        $normalized = trim(preg_replace('/[\-]+/', '', $key));
        if (strlen($normalized) !== 24) {
            throw new InvalidArgumentException('Invalid license key: '.$key);
        }
        return $normalized;
    }

    /**
     * Returns licenses owned by a user.
     *
     * @param int $ownerId
     * @return PluginLicense[]
     */
    public function getLicensesByOwner(int $ownerId): array
    {
        $results = $this->_createLicenseQuery()
            ->where(['l.ownerId' => $ownerId])
            ->all();

        $licenses = [];
        foreach ($results as $result) {
            $licenses[] = new PluginLicense($result);
        }
        return $licenses;
    }

    /**
     * Returns licenses associated with a given Craft license ID.
     *
     * @param int $cmsLicenseId
     * @return PluginLicense[]
     */
    public function getLicensesByCmsLicense(int $cmsLicenseId): array
    {
        $results = $this->_createLicenseQuery()
            ->where(['l.cmsLicenseId' => $cmsLicenseId])
            ->all();

        $licenses = [];
        foreach ($results as $result) {
            $licenses[] = new PluginLicense($result);
        }
        return $licenses;
    }

    /**
     * Returns a license by its key.
     *
     * @param string $handle the plugin handle
     * @param string $key
     * @return PluginLicense
     * @throws LicenseNotFoundException if $key is missing
     */
    public function getLicenseByKey(string $handle, string $key): PluginLicense
    {
        $key = $this->normalizeKey($key);

        $result = $this->_createLicenseQuery()
            ->innerJoin('craftcom_plugins p', '[[p.id]] = [[l.pluginId]]')
            ->where([
                'p.handle' => $handle,
                'l.key' => $key,
            ])
            ->one();

        if ($result === null) {
            throw new LicenseNotFoundException($key);
        }

        return new PluginLicense($result);
    }

    /**
     * Returns licenses by CMS license ID.
     *
     * @param int $cmsLicenseId
     * @return PluginLicense[]
     */
    public function getLicensesByCmsLicenseId(int $cmsLicenseId): array
    {
        $results = $this->_createLicenseQuery()
            ->where(['cmsLicenseId' => $cmsLicenseId])
            ->all();

        $licenses = [];
        foreach ($results as $result) {
            $licenses[] = new PluginLicense($result);
        }
        return $licenses;
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

        if (!$license->pluginId) {
            $license->pluginId = (int)Plugin::find()
                ->select('elements.id')
                ->handle($license->plugin)
                ->scalar();

            if ($license->pluginId === false) {
                throw new Exception("Invalid plugin handle: {$license->plugin}");
            }
        }

        if (!$license->editionId) {
            $license->editionId = (int)PluginEdition::find()
                ->select('elements.id')
                ->pluginId($license->pluginId)
                ->handle($license->edition)
                ->scalar();

            if ($license->editionId === false) {
                throw new Exception("Invalid plugin edition: {$license->edition}");
            }
        }

        $data = [
            'pluginId' => $license->pluginId,
            'editionId' => $license->editionId,
            'ownerId' => $license->ownerId,
            'cmsLicenseId' => $license->cmsLicenseId,
            'plugin' => $license->plugin,
            'edition' => $license->edition,
            'expirable' => $license->expirable,
            'expired' => $license->expired,
            'autoRenew' => $license->autoRenew,
            'email' => $license->email,
            'key' => $license->key,
            'notes' => $license->notes,
            'privateNotes' => $license->privateNotes,
            'lastVersion' => $license->lastVersion,
            'lastAllowedVersion' => $license->lastAllowedVersion,
            'lastActivityOn' => Db::prepareDateForDb($license->lastActivityOn),
            'lastRenewedOn' => Db::prepareDateForDb($license->lastRenewedOn),
            'expiresOn' => Db::prepareDateForDb($license->expiresOn),
            'dateCreated' => Db::prepareDateForDb($license->dateCreated),
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
     * Finds unclaimed license by key and assigns it to the user.
     *
     * @param User $user
     * @param string $key
     *
     * @return bool
     * @throws Exception
     * @throws LicenseNotFoundException
     */
    public function claimLicense(User $user, string $key)
    {
        $key = $this->normalizeKey($key);

        $result = $this->_createLicenseQuery()
            ->innerJoin('craftcom_plugins p', '[[p.id]] = [[l.pluginId]]')
            ->where([
                'l.key' => $key,
            ])
            ->one();

        if ($result === null) {
            throw new LicenseNotFoundException($key);
        }

        $license = new PluginLicense($result);

        if ($user) {
            if (!$license->ownerId) {
                $license->ownerId = $user->id;

                if ($this->saveLicense($license)) {
                    return true;
                }

                throw new Exception("Couldn't save license.");
            }

            throw new Exception("License has already been claimed.");
        }

        throw new LicenseNotFoundException($key);
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
                ->from(['craftcom_pluginlicenses l'])
                ->innerJoin('craftcom_pluginlicenses_lineitems l_li', '[[l_li.licenseId]] = [[l.id]]')
                ->innerJoin('commerce_lineitems li', '[[li.id]] = [[l_li.lineItemId]]')
                ->where(['l.ownerId' => null, 'li.orderId' => $orderIds])
                ->column();

            if (!empty($cmsLicenseIds)) {
                Craft::$app->getDb()->createCommand()
                    ->update('craftcom_pluginlicenses', [
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
        $results = Module::getInstance()->getPluginLicenseManager()->getLicensesByOwner($owner->id);

        $licenses = [];

        foreach ($results as $result) {
            $license = $result->toArray();


            // Plugin

            $plugin = null;

            if ($result->pluginId) {
                $pluginResult = Plugin::find()->id($result->pluginId)->status(null)->one();
                $plugin = $pluginResult->getAttributes(['name']);
            }

            $license['plugin'] = $plugin;


            // CMS License

            $cmsLicense = null;

            if ($result->cmsLicenseId) {
                $cmsLicenseResult = Module::getInstance()->getCmsLicenseManager()->getLicenseById($result->cmsLicenseId);

                if ($cmsLicenseResult && $cmsLicenseResult->ownerId === $owner->id) {
                    $cmsLicense = $cmsLicenseResult->getAttributes(['key']);
                } else {
                    $cmsLicense = [
                        'shortKey' => substr($cmsLicenseResult->key, 0, 10)
                    ];
                }
            }

            $license['cmsLicense'] = $cmsLicense;

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
                'l.id',
                'l.pluginId',
                'l.editionId',
                'l.ownerId',
                'l.cmsLicenseId',
                'l.plugin',
                'l.edition',
                'l.expirable',
                'l.expired',
                'l.autoRenew',
                'l.email',
                'l.key',
                'l.notes',
                'l.privateNotes',
                'l.lastVersion',
                'l.lastAllowedVersion',
                'l.lastActivityOn',
                'l.lastRenewedOn',
                'l.expiresOn',
                'l.dateCreated',
                'l.dateUpdated',
                'l.uid',
            ])
            ->from(['craftcom_pluginlicenses l']);
    }
}
