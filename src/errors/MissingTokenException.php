<?php

namespace craftnet\errors;

use craftnet\composer\Package;
use Throwable;
use yii\base\Exception;

class MissingTokenException extends Exception
{
    /**
     * @var Package
     */
    public $plugin;

    /**
     * Constructor
     *
     * @param Package $package
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(Package $package, string $message = null, $code = 0, Throwable $previous = null)
    {
        $this->plugin = $package;

        if ($message === null) {
            $message = "Package \"{$package->name}\" is missing its VCS token.";
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Missing VCS Token';
    }
}
