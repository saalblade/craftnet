<?php

namespace craftcom\errors;

use Throwable;
use yii\base\Exception;

class MissingStripeAccountException extends Exception
{
    /**
     * Constructor.
     *
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = null, int $code = 0, Throwable $previous = null)
    {
        if ($message === null) {
            $message = "Developer's Stripe account is not known";
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Missing Stripe Account';
    }
}
