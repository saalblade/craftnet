<?php

namespace craftcom\oauthserver\server\Entities;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

/**
 * Class ClientEntity
 *
 * @package craftcom\oauthserver\server\Entities
 */
class ClientEntity implements ClientEntityInterface
{
    // Traits
    // =========================================================================

    use EntityTrait;
    use ClientTrait;

    // Public Methods
    // =========================================================================

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param $redirectUri
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }
}