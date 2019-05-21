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
use craft\db\Query;
use craft\helpers\Json;
use craft\web\Controller;
use craftnet\cms\CmsEdition;
use craftnet\cms\CmsLicense;
use craftnet\cms\CmsPurchasable;
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
//        $headers->set('X-Frame-Options', 'allow-from https://app.frontapp.com');
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
        $key = $request->getParam('key','');
        $key = trim(preg_replace('/\s+/', '', $key));

        //TODO move to real key
        $key = '/^$C5$7WFW9SLIBU6IQ4+3ZQ$4&GLQ=X0DR^+MC+QR28YQUX3N1+9%GS3XH&#ZYTE8I10Z8MZY645RM9/ZAC=GY0D!R1P4Z6&MFJXINH$8L%PTPY9D=3AVGDRB2GRGR0^4M%A*^NTMHE0U%D*##S98DH1KM^PG$IBTH09U1WKT9+8AE%DIC^TR=I7IK5$^Q%WN7X1JMZ+64FNWO=KR$LB!G0Q7Z9XD+VFMA%SZSYUBXV!4JER$$0QGOD4^';

        $license = (new Query())
            ->select(['*'])
            ->from('{{%craftnet_cmslicenses}}')
            ->where(['key'=> $key])
            ->one();

        if (!$license)
        {
            return $this->asErrorJson('No license found.');
        }

        /** @var CmsEdition $edition */
        $edition = CmsEdition::find()->id($license['editionId'])->one();
        $license['editionName'] = $edition->name ?? '';
        $license['editionPrice'] = $edition->price ?? '';

        $data = [];
        $data['license'] = $license;
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
