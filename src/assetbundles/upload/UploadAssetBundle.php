<?php
namespace findarace\upload\assetbundles\upload;

use Craft;

use yii\web\AssetBundle;
use craft\web\assets\cp\CpAsset;


class UploadAssetBundle extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->sourcePath = "@findarace/upload/assetbundles/upload/build";

        $this->depends = [];

        $this->js = [
            'js/vendor/polyfill.js',
            'js/vendor/ready.js',
            'js/vendor/extend.js',
            'js/vendor/Sortable.js',
            'js/Upload.js',
        ];

        $this->css = [
            'css/styles.css',
        ];

        parent::init();
    }
}
