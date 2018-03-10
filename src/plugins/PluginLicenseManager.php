<?php

namespace craftcom\plugins;

use Craft;
use craft\commerce\elements\Order;
use craft\db\Query;
use craft\elements\User;
use craft\helpers\Db;
use craftcom\errors\LicenseNotFoundException;
use yii\base\Component;
use yii\base\Exception;

class PluginLicenseManager extends Component
{
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
            'ownerId' => $license->ownerId,
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
                'l.expirable',
                'l.expired',
                'l.email',
                'l.key',
                'l.notes',
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
