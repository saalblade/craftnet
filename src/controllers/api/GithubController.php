<?php

namespace craftcom\controllers\api;

use yii\web\NotFoundHttpException;

/**
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  3.0
 */
class GithubController extends BaseApiController
{
    public function actionPush()
    {
        $payload = $this->getPayload('github-push-request');
        $url = $payload->repository->url;
        $packageManager = $this->module->getPackageManager();
        $name = $packageManager->getPackageNameByRepoUrl($url);

        if (!$name) {
            throw new NotFoundHttpException('No package exists for the repository '.$url);
        }

        $packageManager->updatePackage($name, false, true);
        $this->module->getJsonDumper()->dump(true);
    }
}
