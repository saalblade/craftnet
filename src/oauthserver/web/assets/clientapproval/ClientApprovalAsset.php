<?php
namespace craftcom\oauthserver\web\assets\clientapproval;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class ClientApprovalAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__.'/dist';

        // define the dependencies
/*        $this->depends = [
            CpAsset::class,
        ];*/

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->css = [
            'reset.css',
            'client-approval.css',
        ];

        parent::init();
    }
}