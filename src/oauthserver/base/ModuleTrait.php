<?php

namespace craftcom\oauthserver\base;

use craftcom\oauthserver\Module as OauthServer;

/**
 * ModuleTrait implements the common methods and properties for plugin classes.
 *
 * @property \craftcom\oauthserver\services\AccessTokens $accessTokens       The accessTokens service
 * @property \craftcom\oauthserver\services\AuthCodes $authCodes          The authCodes service
 * @property \craftcom\oauthserver\services\clients $clients            The clients service
 * @property \craftcom\oauthserver\services\Oauth $oauth              The oauth service
 * @property \craftcom\oauthserver\services\RefreshTokens $refreshTokens      The tokens service
 */
trait ModuleTrait
{
    /**
     * Returns the accessTokens service.
     *
     * @return \craftcom\oauthserver\services\AccessTokens The accessTokens service
     */
    public function getAccessTokens()
    {
        /** @var OauthServer $this */
        return $this->get('accessTokens');
    }

    /**
     * Returns the authCodes service.
     *
     * @return \craftcom\oauthserver\services\AuthCodes The authCodes service
     */
    public function getAuthCodes()
    {
        /** @var OauthServer $this */
        return $this->get('authCodes');
    }

    /**
     * Returns the clients service.
     *
     * @return \craftcom\oauthserver\services\Clients The clients service
     */
    public function getClients()
    {
        /** @var OauthServer $this */
        return $this->get('clients');
    }

    /**
     * Returns the oauth service.
     *
     * @return \craftcom\oauthserver\services\Oauth The oauth service
     */
    public function getOauth()
    {
        /** @var OauthServer $this */
        return $this->get('oauth');
    }

    /**
     * Returns the refreshTokens service.
     *
     * @return \craftcom\oauthserver\services\RefreshTokens The refreshTokens service
     */
    public function getRefreshTokens()
    {
        /** @var OauthServer $this */
        return $this->get('refreshTokens');
    }
}
