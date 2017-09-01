<?php

namespace craftcom\oauthserver\server\Entities;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

/**
 * Class AccessTokenEntity
 *
 * @package craftcom\oauthserver\server\Entities
 */
class AccessTokenEntity implements AccessTokenEntityInterface
{
    // Traits
    // =========================================================================

    use AccessTokenTrait;
    use EntityTrait;
    use TokenEntityTrait;
}