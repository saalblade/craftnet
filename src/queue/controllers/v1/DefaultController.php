<?php

namespace craftcom\queue\controllers\v1;

use Craft;
use craftcom\queue\controllers\BaseApiController;
use craftcom\queue\jobs\TestJob;
use yii\web\Response;

/**
 * Class DefaultController
 *
 * @package craftcom\queue\controllers\v1
 */
class DefaultController extends BaseApiController
{
    /**
     * Handles /v1/createJobs requests.
     *
     * @return Response
     */
    public function actionCreateJobs(): Response
    {
        //$body = $this->getRequestBody('updates-request');
        $numJobs = Craft::$app->getRequest()->getParam('numJobs');

        for ($counter = 0; $counter < $numJobs; $counter++)
        {
            $job = new TestJob();
            Craft::$app->getQueue()->push($job);
        }

        return $this->asRaw('Added '.$numJobs.' jobs.');
    }
}
