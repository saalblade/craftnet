<?php

namespace craftcom\api;

use Craft;

class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        Craft::setAlias('@craftcom/api/controllers', __DIR__.'/controllers');
        parent::init();
    }
}
