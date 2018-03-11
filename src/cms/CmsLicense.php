<?php

namespace craftcom\cms;

use craft\base\Model;
use craft\helpers\ArrayHelper;
use craftcom\Module;
use craftcom\plugins\PluginLicense;

/**
 * @property PluginLicense[] $pluginLicenses
 */
class CmsLicense extends Model
{
    public $id;
    public $editionId;
    public $ownerId;
    public $expirable = true;
    public $expired;
    public $edition;
    public $email;
    public $domain;
    public $key;
    public $notes;
    public $lastEdition;
    public $lastVersion;
    public $lastAllowedVersion;
    public $lastActivityOn;
    public $lastRenewedOn;
    public $expiresOn;
    public $dateCreated;
    public $dateUpdated;
    public $uid;

    public function rules()
    {
        return [
            [['expirable', 'expired', 'edition', 'email', 'key'], 'required'],
            [['id', 'editionId', 'ownerId'], 'number', 'integerOnly' => true, 'min' => 1],
            [
                ['edition'], 'in', 'range' => [
                CmsLicenseManager::EDITION_PERSONAL,
                CmsLicenseManager::EDITION_CLIENT,
                CmsLicenseManager::EDITION_PRO,
            ]
            ],
            [['email'], 'email'],
            [['domain'], 'validateDomain'],
        ];
    }

    public function validateDomain()
    {
        $this->domain = Module::getInstance()->getCmsLicenseManager()->normalizeDomain($this->domain);
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        $names = parent::attributes();
        ArrayHelper::removeValue($names, 'notes');
        return $names;
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        return [
            'pluginLicenses',
        ];
    }

    /**
     * Returns plugin licenses associated with this Craft license.
     *
     * @return PluginLicense[]
     */
    public function getPluginLicenses(): array
    {
        return Module::getInstance()->getPluginLicenseManager()->getLicensesByCmsLicense($this->id);
    }
}
