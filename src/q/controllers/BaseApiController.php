<?php

namespace craftcom\q\controllers;

use craft\web\Controller;

/**
 * Class BaseController
 *
 * @package craftcom\q\controllers
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
