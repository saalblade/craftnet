<?php

namespace craftcom\queue\controllers;

use Craft;
use craft\web\Controller;

/**
 * Class BaseController
 *
 * @package craftcom\queue\controllers
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
