<?php

namespace craftnet\controllers;

use Craft;
use craft\web\Controller;
use craftnet\Module;
use yii\web\BadRequestHttpException;

/**
 * @property Module $module
 */
class UpdateDepsController extends Controller
{
    // Properties
    // =========================================================================

    protected $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    public function actionIndex()
    {
        $secret = Craft::$app->getRequest()->getRequiredParam('secret');
        if ($secret !== getenv('UPDATE_DEPS_SECRET')) {
            throw new BadRequestHttpException();
        }

        $this->module->getPackageManager()->updateDeps(false, true);
    }
}
