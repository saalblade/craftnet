<?php

namespace craftcom\oauthserver\controllers;

use Craft;
use craft\web\Controller;

/**
 * Class BaseController
 *
 * @package craftcom\oauthserver\controllers
 */
abstract class BaseApiController extends Controller
{
    /**
     * @inheritdoc
     */
    public $allowAnonymous = true;

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;
}
