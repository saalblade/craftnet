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

    public function __construct(Plugin $plugin, $message = null, $code = 0, Throwable $previous = null)
    {
        $this->plugin = $plugin;

        if ($message === null) {
            $message = "Plugin \"{$plugin->name}\" is missing its VCS token.";
        }

        parent::__construct($message, $code, $previous);
    }
}
