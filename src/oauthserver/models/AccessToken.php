<?php

namespace craftcom\oauthserver\models;

use craft\base\Model;
use craft\helpers\Json;
use craftcom\oauthserver\Module as OauthServer;

/**
 * Class AccessToken
 *
 * @package craftcom\oauthserver\models
 */
class AccessToken extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    public $id;

    /**
     * @var
     */
    public $clientId;

    /**
     * @var
     */
    public $userId;

    /**
     * @var
     */
    public $identifier;

    /**
     * @var
     */
    public $expiryDate;

    /**
     * @var
     */
    public $userIdentifier;

    /**
     * @var
     */
    public $scopes;

    /**
     * @var
     */
    public $isRevoked;

    /**
     * @var
     */
    public $dateCreated;

    /**
     * @var
     */
    public $dateUpdated;

    /**
     * @var
     */
    public $uid;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (is_string($this->scopes)) {
            $this->scopes = Json::decode($this->scopes);
        }
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if ($this->clientId) {
            return OauthServer::getInstance()->getClients()->getClientById($this->clientId);
        }
    }

    /**
     * @return RefreshToken
     */
    public function getRefreshToken()
    {
        return OauthServer::getInstance()->getRefreshTokens()->getRefreshTokenByAccessTokenId($this->id);
    }

    /**
     * @return bool
     */
    public function hasExpired()
    {
        $now = new \DateTime();
        $expiryDate = new \DateTime($this->expiryDate);

        if ($now->getTimestamp() > $expiryDate->getTimestamp()) {
            return true;
        }

        return false;
    }
}
