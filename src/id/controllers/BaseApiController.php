<?php

namespace craftcom\id\controllers;

use Craft;
use craft\web\Controller;

/**
 * Class BaseController
 *
 * @package craftcom\id\controllers
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
