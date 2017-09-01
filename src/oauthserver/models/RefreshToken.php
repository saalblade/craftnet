<?php
namespace craftcom\oauthserver\models;

use craft\base\Model;

/**
 * Class RefreshToken
 *
 * @package craftcom\oauthserver\models
 */
class RefreshToken extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    public $id;

    /**
     * @var
     */
    public $accessTokenId;

    /**
     * @var
     */
    public $identifier;

    /**
     * @var
     */
    public $expiryDate;

    /**
     * @var
     */
    public $dateCreated;

    /**
     * @var
     */
    public $dateUpdated;

    /**
     * @var
     */
    public $uid;
}