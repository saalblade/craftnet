<?php

namespace craftnet\errors;

use yii\base\Exception;

class InvalidSvgException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalid SVG';
    }
}
