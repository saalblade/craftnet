<?php

namespace craftcom\controllers\api;

use Craft;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

/**
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  3.0
 */
class GithubController extends BaseApiController
{
    /**
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionPush()
    {
        $payload = $this->getPayload('github-push-request');

        $url = $payload->repository->url;
        $packageManager = $this->module->getPackageManager();
        $name = $packageManager->getPackageNameByRepoUrl($url);

        Craft::error('Received a payload from Github for '.$name.'.', __METHOD__);

        if (!$name) {
            throw new NotFoundHttpException('No package exists for the repository '.$url);
        }

        $package = $packageManager->getPackage($name);
        $this->_validateSecret($package->webhookSecret);

        $packageManager->updatePackage($name, false, true);
        $this->module->getJsonDumper()->dump(true);
    }

    /**
     * @param string|null $secret
     *
     * @throws BadRequestHttpException
     */
    private function _validateSecret($secret)
    {
        $headers = Craft::$app->getRequest()->getHeaders();

        if (!isset($headers['X-Hub-Signature'])) {
            throw new BadRequestHttpException('Invalid request body.');
        }

        list($algo, $hash) = explode('=', $headers['X-Hub-Signature'], 2);

        $payloadHash = hash_hmac($algo, Craft::$app->getRequest()->getRawBody(), $secret);

        if (!hash_equals($payloadHash, $hash)) {
            throw new BadRequestHttpException('Invalid request body.');
        }
    }
}
