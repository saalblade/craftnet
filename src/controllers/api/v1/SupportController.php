<?php

namespace craftnet\controllers\api\v1;

use Craft;
use craft\helpers\StringHelper;
use craft\web\UploadedFile;
use craftnet\controllers\api\BaseApiController;
use GuzzleHttp\RequestOptions;
use yii\web\Response;

/**
 * Class SupportController
 */
class SupportController extends BaseApiController
{
    /**
     * Creates a new support request
     *
     * @return Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCreate(): Response
    {
        $client = Craft::createGuzzleClient([
            'base_uri' => 'https://api2.frontapp.com',
        ]);

        $headers = [
            'Authorization' => 'Bearer ' . getenv('FRONT_TOKEN'),
            'Accept' => 'application/json',
        ];

        $request = Craft::$app->getRequest();
        $parts = [
            [
                'name' => 'sender[handle]',
                'contents' => $request->getRequiredBodyParam('email'),
            ],
            [
                'name' => 'sender[name]',
                'contents' => $request->getRequiredBodyParam('name'),
            ],
            [
                'name' => 'to[]',
                'contents' => getenv('FRONT_TO_EMAIL'),
            ],
            [
                'name' => 'subject',
                'contents' => getenv('FRONT_SUBJECT'),
            ],
            [
                'name' => 'body',
                'contents' => $request->getRequiredBodyParam('message'),
            ],
            [
                'name' => 'body_format',
                'contents' => 'markdown',
            ],
            [
                'name' => 'external_id',
                'contents' => StringHelper::UUID(),
            ],
            [
                'name' => 'created_at',
                'contents' => time(),
            ],
            [
                'name' => 'type',
                'contents' => 'email',
            ],
            [
                'name' => 'tags[]',
                'contents' => getenv('FRONT_TAG'),
            ],
            [
                'name' => 'metadata[thread_ref]',
                'contents' => StringHelper::UUID(),
            ],
            [
                'name' => 'metadata[is_inbound]',
                'contents' => 'true',
            ],
            [
                'name' => 'metadata[is_archived]',
                'contents' => 'false',
            ],
            [
                'name' => 'metadata[should_skip_rules]',
                'contents' => getenv('FRONT_SKIP_RULES') ?: 'true',
            ],
        ];

        if ($attachment = UploadedFile::getInstanceByName('attachment')) {
            $parts[] = [
                'name' => 'attachments[]',
                'contents' => fopen($attachment->tempName, 'rb'),
                'filename' => $attachment->name,
            ];
        }

        $client->post('/inboxes/' . getenv('FRONT_INBOX_ID') . '/imported_messages', [
            RequestOptions::HEADERS => $headers,
            RequestOptions::MULTIPART => $parts,
        ]);

        return $this->asJson([
            'sent' => true,
        ]);
    }
}
