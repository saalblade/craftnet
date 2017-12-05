<?php

namespace craftcom\controllers\api;

use Craft;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

/**
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  3.0
 */
class WebhookController extends BaseApiController
{
    /**
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionGithub()
    {
        $payload = $this->getPayload('github-webhook');
        Craft::info('Incoming payload from Github.', __METHOD__);

        $url = $payload->repository->html_url;
        $packageManager = $this->module->getPackageManager();
        $name = $packageManager->getPackageNameByRepoUrl($url);

        if (!$name) {
            throw new NotFoundHttpException('No package exists for the repository '.$url);
        }

        $package = $packageManager->getPackage($name);
        $this->_validateSecret($package->webhookSecret);

        Craft::info('Updating package: '.$name, __METHOD__);
        $packageManager->updatePackage($name, false, true);
        Craft::info('Dumping JSON', __METHOD__);
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
            Craft::error('Invalid secret from Github payload. Payload Hash: '.$payloadHash.' Hash: '.$hash, __METHOD__);
            throw new BadRequestHttpException('Invalid request body.');
        }
    }
}
