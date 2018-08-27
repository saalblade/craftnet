<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/23/18
 * Time: 2:06 PM
 */

namespace craftnet\partners;


use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class PartnerAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@craftnet/partners/resources';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'partners.js',
        ];

        $this->css = [
            'partners.css',
        ];

        parent::init();
    }
}