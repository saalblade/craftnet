<?php

namespace craftcom\cms;

use craft\base\Model;

class CmsLicense extends Model
{
    public $id;
    public $editionId;
    public $expirable;
    public $expired;
    public $edition;
    public $email;
    public $hostname;
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
            [['id', 'editionId'], 'number', 'integerOnly' => true, 'min' => 1],
            [['edition'], 'in', 'range' => [
                CmsLicenseManager::EDITION_PERSONAL,
                CmsLicenseManager::EDITION_CLIENT,
                CmsLicenseManager::EDITION_PRO,
            ]],
            [['email'], 'email'],
        ];
    }
}
