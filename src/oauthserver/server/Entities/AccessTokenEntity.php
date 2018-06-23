<?php

namespace craftnet\oauthserver\server\Entities;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

/**
 * Class AccessTokenEntity
 */
class AccessTokenEntity implements AccessTokenEntityInterface
{
    // Traits
    // =========================================================================

    use AccessTokenTrait;
    use EntityTrait;
    use TokenEntityTrait;
}
