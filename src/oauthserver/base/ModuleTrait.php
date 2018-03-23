<?php

namespace craftnet\oauthserver\base;

use craftnet\oauthserver\Module as OauthServer;

/**
 * ModuleTrait implements the common methods and properties for plugin classes.
 *
 * @property \craftnet\oauthserver\services\AccessTokens $accessTokens The accessTokens service
 * @property \craftnet\oauthserver\services\AuthCodes $authCodes The authCodes service
 * @property \craftnet\oauthserver\services\clients $clients The clients service
 * @property \craftnet\oauthserver\services\Oauth $oauth The oauth service
 * @property \craftnet\oauthserver\services\RefreshTokens $refreshTokens The tokens service
 */
trait ModuleTrait
{
    /**
     * Returns the accessTokens service.
     *
     * @return \craftnet\oauthserver\services\AccessTokens The accessTokens service
     */
    public function getAccessTokens()
    {
        /** @var OauthServer $this */
        return $this->get('accessTokens');
    }

    /**
     * Returns the authCodes service.
     *
     * @return \craftnet\oauthserver\services\AuthCodes The authCodes service
     */
    public function getAuthCodes()
    {
        /** @var OauthServer $this */
        return $this->get('authCodes');
    }

    /**
     * Returns the clients service.
     *
     * @return \craftnet\oauthserver\services\Clients The clients service
     */
    public function getClients()
    {
        /** @var OauthServer $this */
        return $this->get('clients');
    }

    /**
     * Returns the oauth service.
     *
     * @return \craftnet\oauthserver\services\Oauth The oauth service
     */
    public function getOauth()
    {
        /** @var OauthServer $this */
        return $this->get('oauth');
    }

    /**
     * Returns the refreshTokens service.
     *
     * @return \craftnet\oauthserver\services\RefreshTokens The refreshTokens service
     */
    public function getRefreshTokens()
    {
        /** @var OauthServer $this */
        return $this->get('refreshTokens');
    }
}
