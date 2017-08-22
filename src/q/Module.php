<?php
namespace craftcom\q;

use Craft;

class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        Craft::setAlias('@craftcom/q/controllers', __DIR__.'/controllers');
        parent::init();
    }
}
