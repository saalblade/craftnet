<?php

namespace craftcom\id\controllers\v1;

use craftcom\id\controllers\BaseApiController;
use yii\web\Response;

/**
 * Class IdController
 *
 * @package craftcom\id\controllers\v1
 */
class IdController extends BaseApiController
{
    /**
     * Handles /v1/id requests.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        //$body = $this->getRequestBody('updates-request');

        //$client = new \Github\Client();
        //$token = $client->api('apps')->createInstallationToken(567313);

        return $this->asJson([
            'cms' => [
                'status' => 'eligible',
                'updates' => [
                    [
                        'version' => '3.0.1',
                        'date' => '2018-01-02T00:00:00-08:00',
                        'notes' => 'Release notes',
                        'critical' => true
                    ],
                    [
                        'version' => '3.0.2',
                        'date' => '2018-01-01T00:00:00-08:00',
                        'notes' => 'Release notes'
                    ]
                ]
            ],
            'plugins' => [
                'nystudio107/seomatic' => [
                    'status' => 'eligible',
                    'updates' => [
                        [
                            'version' => '2.0.1',
                            'date' => '2018-01-02T00:00:00-08:00',
                            'notes' => 'Release notes',
                            'critical' => true
                        ],
                        [
                            'version' => '2.0.2',
                            'date' => '2018-01-01T00:00:00-08:00',
                            'notes' => 'Release notes'
                        ]
                    ]
                ]
            ]
        ]);
    }
}
