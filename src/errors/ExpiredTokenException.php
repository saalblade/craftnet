<?php

namespace craftnet\errors;

use Throwable;
use yii\base\Exception;

class ExpiredTokenException extends Exception
{
    /**
     * Constructor
     *
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = null, int $code = 0, Throwable $previous = null)
    {
        if ($message === null) {
            $message = 'The access token has expired.';
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Expired Token';
    }
}
