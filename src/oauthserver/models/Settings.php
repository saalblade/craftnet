<?php

namespace craftnet\oauthserver\models;

use craft\base\Model;

/**
 * Class Settings
 *
 * @package namespace craftnet\oauthserver\models
 */
class Settings extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public $accessTokenExpiry = 'PT1H';

    /**
     * @var string
     */
    public $refreshTokenExpiry = 'P1M';

    /**
     * @var string
     */
    public $authCodeExpiry = 'P1M';

    /**
     * @var string|null
     */
    public $clientApprovalTemplate;

    /**
     * @var array
     */
    public $enabledGrants = [
        'ClientCredentialsGrant',
        'PasswordGrant',
        'RefreshTokenGrant',
        'ImplicitGrant',
        'AuthCodeGrant',
    ];

    /**
     * @var array
     */
    public $grants = [
        'ClientCredentialsGrant' => 'Client Credentials Grant',
        'PasswordGrant' => 'Password Grant',
        'AuthCodeGrant' => 'Authorization Code Grant',
        'ImplicitGrant' => 'Implicit Grant',
        'RefreshTokenGrant' => 'Refresh Token Grant',
    ];

    /**
     * @var string|null
     */
    public $privateKey;

    /**
     * @var string|null
     */
    public $publicKey;

    /**
     * @var string|null
     */
    public $encryptionKey;

    /**
     * @var array
     */
    public $scopes = [];
}
