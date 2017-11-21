<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   MIT
 */

namespace pixelandtonic\yii\queue\sqs;

use yii\web\Controller;

/**
 * Manages the SQS queue
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 */
class WebController extends Controller
{
    /**
     * @var Queue
     */
    public $queue;

    /**
     * Handles an incoming SQS message.
     */
    public function actionHandleMessage()
    {
        $request = \Yii::$app->getRequest();
        $headers = $request->getHeaders();

        $id = $headers->get('X-Aws-Sqsd-Msgid');
        $ttr = $headers->get('X-Aws-Sqsd-Attr-TTR');
        $attempt = $headers->get('X-Aws-Sqsd-Receive-Count');
        $message = $request->getRawBody();

        $this->queue->handle($id, $message, $ttr, $attempt);
    }

    /**
     * Runs new SQS jobs as they show up.
     *
     * @param int $delay
     */
    public function actionListen($delay = 3)
    {
        $this->queue->listen($delay);
    }
}
