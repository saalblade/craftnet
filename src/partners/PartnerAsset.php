<?php

namespace craftnet\partners;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\vue\VueAsset;


class PartnerAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@craftnet/partners/resources';

        $this->depends = [
            CpAsset::class,
            VueAsset::class,
        ];

        $this->js = [
            'partners.js',
            'partnerhistory.js',
        ];

        $this->css = [
            'partners.css',
        ];

        parent::init();
    }
}
