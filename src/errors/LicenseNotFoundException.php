<?php

namespace craftnet\errors;

use Throwable;
use yii\base\Exception;

class LicenseNotFoundException extends Exception
{
    /**
     * @var string Missing license ID or key
     */
    public $identifier;

    /**
     * Constructor
     *
     * @param mixed $identifier
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($identifier, string $message = null, int $code = 0, Throwable $previous = null)
    {
        $this->identifier = $identifier;

        if ($message === null) {
            $message = 'License not found: '.$identifier;
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'License not found';
    }
}
