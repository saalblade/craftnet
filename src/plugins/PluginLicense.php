<?php

namespace craftcom\plugins;

use craft\base\Model;
use craft\helpers\ArrayHelper;

class PluginLicense extends Model
{
    public $id;
    public $pluginId;
    public $editionId;
    public $ownerId;
    public $cmsLicenseId;
    public $plugin;
    public $edition;
    public $expirable = true;
    public $expired;
    public $email;
    public $key;
    public $notes;
    public $privateNotes;
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
            [['expirable', 'expired', 'plugin', 'edition', 'email', 'key'], 'required'],
            [['id', 'pluginId', 'editionId', 'ownerId', 'cmsLicenseId'], 'number', 'integerOnly' => true, 'min' => 1],
            [['email'], 'email'],
        ];
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
            'plugin',
        ];
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return Plugin::find()
            ->id($this->pluginId)
            ->status(null)
            ->one();
    }
}
