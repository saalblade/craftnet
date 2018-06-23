<?php

namespace craftnet\oauthserver\server\Repositories;

use craftnet\oauthserver\models\AccessToken;
use craftnet\oauthserver\Module as OauthServer;
use craftnet\oauthserver\server\Entities\AccessTokenEntity;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    // Public Methods
    // =========================================================================
    /**
     * Create a new access token
     *
     * @param ClientEntityInterface $clientEntity
     * @param ScopeEntityInterface[] $scopes
     * @param mixed $userIdentifier
     *
     * @return AccessTokenEntityInterface
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        return new AccessTokenEntity();
    }

    /**
     * Persists a new access token to permanent storage.
     *
     * @param AccessTokenEntityInterface $accessTokenEntity
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $clientIdentifier = $accessTokenEntity->getClient()->getIdentifier();
        $client = OauthServer::getInstance()->getClients()->getClientByIdentifier($clientIdentifier);

        $accessToken = new AccessToken;
        $accessToken->clientId = $client->id;
        $accessToken->userId = $accessTokenEntity->getUserIdentifier();
        $accessToken->identifier = $accessTokenEntity->getIdentifier();
        $accessToken->expiryDate = $accessTokenEntity->getExpiryDateTime();
        $accessToken->scopes = $accessTokenEntity->getScopes();
        $accessToken->isRevoked = false;

        OauthServer::getInstance()->getAccessTokens()->saveAccessToken($accessToken);
    }

    /**
     * Revoke an access token.
     *
     * @param string $identifier
     */
    public function revokeAccessToken($identifier)
    {
        $accessToken = OauthServer::getInstance()->getAccessTokens()->getAccessTokenByIdentifier($identifier);

        if ($accessToken) {
            $accessToken->isRevoked = true;

            OauthServer::getInstance()->getAccessTokens()->saveAccessToken($accessToken);
        } else {
            throw new \Exception("Access Token not found.");
        }
    }

    /**
     * Check if the access token has been revoked.
     *
     * @param string $identifier
     *
     * @return bool Return true if this token has been revoked
     */
    public function isAccessTokenRevoked($identifier)
    {
        $accessToken = OauthServer::getInstance()->getAccessTokens()->getAccessTokenByIdentifier($identifier);

        if ($accessToken) {
            if ($accessToken->isRevoked) {
                return true;
            }
        }

        return false;
    }
}
