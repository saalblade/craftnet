<?php

namespace craftcom\oauthserver\server\Entities;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

/**
 * Class ScopeEntity
 *
 * @package craftcom\oauthserver\server\Entities
 */
class ScopeEntity implements ScopeEntityInterface
{
    // Traits
    // =========================================================================

    use EntityTrait;

    // Public Methods
    // =========================================================================

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *        which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->identifier;
    }
}
