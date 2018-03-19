<?php

namespace craftnet\errors;

use Craft;
use Throwable;
use yii\base\Exception;

class InsufficientFundsException extends Exception
{
    /**
     * @var float
     */
    public $balance;

    /**
     * Constructor.
     *
     * @param float $balance
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(float $balance, string $message = null, int $code = 0, Throwable $previous = null)
    {
        $this->balance = $balance;

        if ($message === null) {
            $formatted = Craft::$app->getFormatter()->asCurrency($balance, 'USD');
            $message = "Insufficient funds ({$formatted})";
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Insufficient Funds';
    }
}
