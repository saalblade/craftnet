<?php

namespace craftcom\oauthserver\server\Entities;

use League\OAuth2\Server\Entities\UserEntityInterface;

/**
 * Class UserEntity
 *
 * @package craftcom\oauthserver\server\Entities
 */
class UserEntity implements UserEntityInterface
{
    // Traits
    // =========================================================================

    use \League\OAuth2\Server\Entities\Traits\EntityTrait;
}