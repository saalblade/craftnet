<?php

namespace craftnet\oauthserver\server\Entities;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;

/**
 * Class RefreshTokenEntity
 */
class RefreshTokenEntity implements RefreshTokenEntityInterface
{
    // Traits
    // =========================================================================

    use EntityTrait;
    use RefreshTokenTrait;
    use ClientTrait;
}
