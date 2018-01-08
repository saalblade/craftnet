<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   MIT
 */

namespace pixelandtonic\yii\queue\sqs;

/**
 * Manages the SQS queue
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class ConsoleCommand extends \yii\queue\cli\Command
{
    /**
     * @var Queue
     */
    public $queue;

    /**
     * Runs all jobs from SQS.
     */
    public function actionRun()
    {
        $this->queue->run();
    }

    /**
     * Runs new SQS jobs as they show up.
     *
     * @param integer $delay
     */
    public function actionListen($delay = 3)
    {
        $this->queue->listen($delay);
    }

    /**
     * @inheritdoc
     */
    protected function isWorkerAction($actionID)
    {
        return in_array($actionID, ['run', 'listen'], true);
    }
}
