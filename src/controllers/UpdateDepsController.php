<?php

namespace craftcom\controllers;

use Craft;
use craft\web\Controller;
use craftcom\Module;
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

        Craft::info('Starting to update package dependencies.', __METHOD__);

        $this->module->getPackageManager()->updateDeps(false, true);

        Craft::info('Done updating package dependencies.', __METHOD__);
    }
}
