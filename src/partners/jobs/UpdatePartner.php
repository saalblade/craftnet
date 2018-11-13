<?php

namespace craftnet\partners\jobs;

use craft\queue\BaseJob;
use craft\queue\QueueInterface;
use yii\base\NotSupportedException;
use yii\queue\Queue;

class UpdatePartner extends BaseJob
{
    /**
     * @var int
     */
    public $partnerId;

    /**
     * @param QueueInterface|Queue $queue
     * @throws NotSupportedException
     */
    public function execute($queue)
    {
        throw new NotSupportedException('Executing Updated Partner jobs is not supported.');
    }
}
