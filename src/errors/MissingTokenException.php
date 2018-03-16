<?php

namespace craftcom\errors;

use craftcom\plugins\Plugin;
use Throwable;
use yii\base\Exception;

class MissingTokenException extends Exception
{
    /**
     * @var Plugin
     */
    public $plugin;

    /**
     * Constructor
     *
     * @param Plugin $plugin
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(Plugin $plugin, string $message = null, $code = 0, Throwable $previous = null)
    {
        $this->plugin = $plugin;

        if ($message === null) {
            $message = "Plugin \"{$plugin->name}\" is missing its VCS token.";
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
