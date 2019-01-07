<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   MIT
 */

namespace pixelandtonic\yii\queue\sqs;

use Aws\Sqs\SqsClient;
use Craft;
use yii\base\InvalidConfigException;
use yii\queue\serializers\JsonSerializer;
use yii\web\Application as WebApp;

/**
 * SQS Queue
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 */
class Queue extends \yii\queue\cli\Queue
{
    /**
     * @var string The SQS queue URL
     */
    public $url;

    /**
     * @var SqsClient The SQS client. This can initially be set to an SQS client config array.
     */
    public $client;

    /**
     * @var int|null
     */
    public $messageDeduplicationId;

    /**
     * @var string command class name
     */
    public $commandClass = ConsoleCommand::class;

    /**
     * @inheritdoc
     */
    public $serializer = JsonSerializer::class;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!is_string($this->url)) {
            throw new InvalidConfigException('queueUrl must be set to the SQS queue URL.');
        }

        if (is_array($this->client)) {
            $this->client = new SqsClient($this->client);
        }
        if (!$this->client instanceof SqsClient) {
            throw new InvalidConfigException('client must be an SqsClient object.');
        }
    }

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        parent::bootstrap($app);

        if ($app instanceof WebApp) {
            $app->controllerMap[$this->getCommandId()] = [
                'class' => WebController::class,
                'queue' => $this,
            ];
        }
    }

    /**
     * Runs all jobs from queue.
     */
    public function run()
    {
        while ($message = $this->reserve()) {
            $ttr = $message['MessageAttributes']['TTR']['StringValue'] ?? $this->ttr;
            if ($this->handleMessage($message['MessageId'], $message['Body'], $ttr, $message['Attributes']['ApproximateReceiveCount'])) {
                $this->remove($message);
            }
        }
    }

    /**
     * Handles a message
     *
     * @param string|null $id
     * @param string      $message
     * @param int         $ttr
     * @param int         $attempt
     *
     * @return bool
     */
    public function handle($id, $message, $ttr, $attempt): bool
    {
        return $this->handleMessage($id, $message, $ttr, $attempt);
    }

    /**
     * @inheritdoc
     */
    public function handleError($id, $job, $ttr, $attempt, $error)
    {
        // Log the exception
        $e = new \Exception('Error handling queue message: '.$error->getMessage(), 0, $error);
        Craft::$app->getErrorHandler()->logException($e);

        return parent::handleError($id, $job, $ttr, $attempt, $error);
    }

    /**
     * Listens queue and runs new jobs.
     *
     * @param int $delay
     */
    public function listen(int $delay)
    {
        do {
            $this->run();
        } while (!$delay || sleep($delay) === 0);
    }

    /**
     * @inheritdoc
     */
    protected function pushMessage($message, $ttr, $delay, $priority)
    {
        $result = $this->client->sendMessage([
            'QueueUrl' => $this->url,
            'MessageBody' => $message,
            'DelaySeconds' => $delay,
            'MessageAttributes' => [
                'TTR' => [
                    'StringValue' => (string)$this->ttr,
                    'DataType' => 'Number',
                ],
            ],
        ]);

        return $result['MessageId'] ?? null;
    }

    /**
     * Returns the next message
     *
     * @return array|null
     */
    protected function reserve()
    {
        $result = $this->client->receiveMessage([
            'QueueUrl' => $this->url,
            'AttributeNames' => ['ApproximateReceiveCount'],
            'MessageAttributeNames' => ['TTR'],
        ]);
        return $result['Messages'][0] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function status($id)
    {
        // No way to know what's going on with SQS
        return 0;
    }

    /**
     * Removes a message from the queue
     *
     * @param array $message The message
     */
    public function remove($message)
    {
        $this->client->deleteMessage([
            'QueueUrl' => $this->url,
            'ReceiptHandle' => $message['ReceiptHandle'],
        ]);
    }
}
