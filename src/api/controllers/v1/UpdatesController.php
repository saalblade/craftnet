<?php

namespace craftcom\api\controllers\v1;

use craftcom\api\controllers\BaseApiController;
use yii\web\Response;

/**
 * Class UpdatesController
 *
 * @package craftcom\api\controllers\v1
 */
class UpdatesController extends BaseApiController
{
    /**
     * Handles /v1/updates requests.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        //$body = $this->getRequestBody('updates-request');

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
