<?php
namespace craftcom\queue;

use Craft;

class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        Craft::setAlias('@craftcom/queue/controllers', __DIR__.'/controllers');
        parent::init();
    }
}
