<?php
/**
 * Front module for Craft CMS 3.x
 *
 * Front integration
 *
 * @link      https://craftcms.com
 * @copyright Copyright (c) 2019 Luke Holder
 */

namespace modules\frontmodule\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * @author    Luke Holder
 * @package   FrontModule
 * @since     1.0.0
 */
class FrontController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected $allowAnonymous = true;

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    // Public Methods
    // =========================================================================

    /**
     * This is the main sidebar in front.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $headers = Craft::$app->getResponse()->getHeaders();
        $headers->set('X-Frame-Options', 'allow-from https://app.frontapp.com');
        $request = Craft::$app->getRequest();
        $authSecret = $request->getQueryParam('auth_secret');

        $secureOn = false; // change this in dev etc.

        if ($secureOn) {
            if (!$authSecret || !hash_equals($authSecret, getenv('FRONT_AUTH_SECRET'))) {
                return $this->renderTemplate('front-module/not-allowed.twig', []);
            }
        }

        return $this->renderTemplate('front-module/index.twig', []);
    }

    /**
     * @return Response
     */
    public function actionGetLicenseInfo(): Response
    {
        $request = Craft::$app->getRequest();
        $key = $request->getParam('key');

        if (!$key) // TODO (or key not found in DB.)
        {
            return $this->asErrorJson('Key not found');
        }

        $data = [];
        // TODO populate key info with real data from lookup
        $data['keyInfo'] = [
            'edition' => 'pro'
        ];
        $data['success'] = true;
        return $this->asJson($data);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionScrubConversation(): Response
    {
        $conversationId = Craft::$app->getRequest()->getRequiredBodyParam('conversationId');
        $token = getenv('FRONT_TOKEN');

        // request conversation details
        $apiHost = 'https://api2.frontapp.com/';
        $config = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]
        ];

        $client = Craft::createGuzzleClient($config);
        $response = $client->request('GET', $apiHost . 'conversations/' . $conversationId);
        $conversationData = Json::decodeIfJson($response->getBody()->getContents());

        $recipient = null;
        $licenseKey = null;
        $pattern = '/(?:\S{50}\s{0,2}){5}/';

        if (is_array($conversationData)) {
            $recipient = $conversationData['recipient']['handle'] ?? null;
        }

        $response = $response = $client->request('GET', $apiHost . 'conversations/' . $conversationId . '/messages');
        $messages = Json::decodeIfJson($response->getBody()->getContents());

        if (is_array($messages)) {
            $messages = $messages['_results'] ?? [];

            foreach ($messages as $message) {
                if (!empty($message['attachments'])) {
                    foreach ($message['attachments'] as $attachment) {
                        if (!empty($attachment['filename']) && $attachment['filename'] == 'license.key' && $attachment['size'] < 512) {
                            $licenseKey = $client->request('GET', $attachment['url'])->getBody()->getContents();
                            break 2;
                        }
                    }
                }

                if (preg_match($pattern, $message['text'] ?? (strip_tags($message['body']) ?? ''), $matches)) {
                    $licenseKey = $matches[0];
                    break;
                }
            }
        }

        $data['licenseKey'] = $licenseKey;
        $data['email'] = $recipient;

        return $this->asJson($data);
    }
}
