<?php

namespace craftcom\controllers\api;

use Craft;
use yii\web\NotFoundHttpException;

/**
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  3.0
 */
class GithubController extends BaseApiController
{
    /**
     * @throws NotFoundHttpException
     */
    public function actionPush()
    {
        $payload = $this->getPayload('github-push-request');

        $url = $payload->repository->url;
        $packageManager = $this->module->getPackageManager();
        $name = $packageManager->getPackageNameByRepoUrl($url);

        if (!$name) {
            throw new NotFoundHttpException('No package exists for the repository '.$url);
        }

        $package = $packageManager->getPackage($name);
        $this->_validateSecret($payload, $package->webhookToken);

        $packageManager->updatePackage($name, false, true);
        $this->module->getJsonDumper()->dump(true);
    }

    /**
     * @param $payload
     * @param $webhookToken
     */
    private function _validateSecret($payload, $webhookToken)
    {
        $allHeaders = Craft::$app->getRequest()->getHeaders();

        if (!isset($allHeaders['X-Hub-Signature'])) {
            throw new BadRequestHttpException('Invalid request body.');
        }

        $token = $allHeaders['X-Hub-Signature'];
        list($algo, $hash) = explode('=', $token, 2);

        $payloadHash = hash_hmac($algo, $payload, $webhookToken);

        if (!hash_equals($webhookToken, $token)) {
            throw new BadRequestHttpException('Invalid request body.');
        }
    }
}
