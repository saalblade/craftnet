<?php

namespace craftcom\id;

use Craft;

class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        Craft::setAlias('@craftcom/id/controllers', __DIR__.'/controllers');
        parent::init();
    }
}
