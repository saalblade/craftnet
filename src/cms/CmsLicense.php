<?php

namespace craftnet\cms;

use craft\base\Model;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craftnet\base\EditionInterface;
use craftnet\base\LicenseInterface;
use craftnet\Module;
use craftnet\plugins\PluginLicense;

/**
 * @property PluginLicense[] $pluginLicenses
 * @property string $shortKey
 */
class CmsLicense extends Model implements LicenseInterface
{
    public $id;
    public $editionId;
    public $ownerId;
    public $expirable = true;
    public $expired = false;
    public $autoRenew = false;
    public $editionHandle;
    public $email;
    public $domain;
    public $key;
    public $notes;
    public $privateNotes;
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
            [['expirable', 'expired', 'editionHandle', 'email', 'key'], 'required'],
            [['id', 'editionId', 'ownerId'], 'number', 'integerOnly' => true, 'min' => 1],
            [
                ['editionHandle'], 'in', 'range' => [
                CmsLicenseManager::EDITION_SOLO,
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
        ArrayHelper::removeValue($names, 'privateNotes');
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
     * @inheritdoc
     */
    public function datetimeAttributes(): array
    {
        $attributes = parent::datetimeAttributes();
        $attributes[] = 'lastActivityOn';
        $attributes[] = 'lastRenewedOn';
        $attributes[] = 'expiresOn';
        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function getIsExpirable(): bool
    {
        return $this->expirable;
    }

    /**
     * @inheritdoc
     */
    public function getExpiryDate(): \DateTime
    {
        return DateTimeHelper::toDateTime($this->expiresOn);
    }

    /**
     * @inheritdoc
     */
    public function getEdition(): EditionInterface
    {
        return CmsEdition::findOne($this->editionId);
    }

    /**
     * Returns a shortened version of the license key.
     *
     * @return string
     */
    public function getShortKey(): string
    {
        return substr($this->key, 0, 10);
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
