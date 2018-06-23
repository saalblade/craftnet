<?php

namespace craftnet\oauthserver\server\Entities;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

/**
 * Class AuthCodeEntity
 */
class AuthCodeEntity implements AuthCodeEntityInterface
{
    // Traits
    // =========================================================================

    use AuthCodeTrait;
    use TokenEntityTrait;
    use EntityTrait;
}
