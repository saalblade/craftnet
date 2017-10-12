<?php

namespace craftcom\id\controllers;

use craft\web\Controller;

/**
 * Class BaseController
 *
 * @package craftcom\id\controllers
 */
abstract class BaseController extends Controller
{
    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;
}
