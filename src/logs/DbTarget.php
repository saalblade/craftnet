<?php

namespace craftcom\logs;

use Craft;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use yii\helpers\VarDumper;

/**
 * Class Plugin
 *
 * @package craftcom\logs
 */
class DbTarget extends \yii\log\DbTarget
{
    /**
     * @inheritdoc
     */
    public $db = 'logDb';

    /**
     * @inheritdoc
     */
    public $logTable = '{{%logs}}';

    /**
     * @inheritdoc
     */
    public function export()
    {
        // Only log to the DbTarget for API controller requests.
        if (!StringHelper::startsWith(Craft::$app->controller->id, 'api/')) {
            return;
        }

        $tableName = $this->db->quoteTableName($this->logTable);
        $sql = "INSERT INTO $tableName ([[requestId]], [[level]], [[category]], [[message]], [[dateCreated]])
                VALUES (:requestId, :level, :category, :message, :dateCreated)";
        $command = $this->db->createCommand($sql);

        foreach ($this->messages as $message) {
            list($text, $level, $category, $timestamp) = $message;

            // Only log the messages for our craftcom module.
            if (StringHelper::contains($category, 'craftcom')) {
                if (!is_string($text)) {
                    // exceptions may not be serializable if in the call stack somewhere is a Closure
                    if ($text instanceof \Throwable || $text instanceof \Exception) {
                        $text = (string)$text;
                    } else {
                        $text = VarDumper::export($text);
                    }
                }

                $command->bindValues([
                    ':requestId' => Craft::$app->controller->getLogRequestId(),
                    ':level' => $level,
                    ':category' => $category,
                    ':message' => $text,
                    ':dateCreated' => Db::prepareDateForDb(new \DateTime()),
                ])->execute();
            }
        }
    }
}
