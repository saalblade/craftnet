<?php

namespace craftcom\controllers;

use Craft;
use craft\web\Controller;
use craftcom\Module;
use mikehaertl\shellcommand\Command as ShellCommand;
use yii\web\BadRequestHttpException;

/**
 * @property Module $module
 */
class JobsController extends Controller
{
    // Properties
    // =========================================================================

    protected $allowAnonymous = true;

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $secret = Craft::$app->getRequest()->getRequiredParam('secret');
        if ($secret !== getenv('JOBS_SECRET')) {
            throw new BadRequestHttpException();
        }

        return parent::beforeAction($action);
    }

    // Public Methods
    // =========================================================================

    public function actionUpdateDeps()
    {
        $this->module->getPackageManager()->updateDeps(false, true);
    }

    public function actionSyncStaging()
    {
        $shellCommand = new ShellCommand();
        $shellCommand->setCommand(getenv('SYNC_PATH'));

        $success = $shellCommand->execute();

        if (!$success) {
            Craft::error('There was a problem syncing staging: '.$shellCommand->getError(), __METHOD__);
        }
    }
}
