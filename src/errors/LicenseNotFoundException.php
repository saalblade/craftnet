<?php

namespace craftcom\errors;

use Throwable;
use yii\base\Exception;

class LicenseNotFoundException extends Exception
{
    /**
     * @var string Missing license key
     */
    public $key;

    /**
     * @inheritdoc
     */
    public function __construct(string $key, string $message = null, int $code = 0, Throwable $previous = null)
    {
        $this->key = $key;

        if ($message === null) {
            $message = 'License key not found: '.$key;
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
