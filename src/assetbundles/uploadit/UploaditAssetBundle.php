<?php
namespace presseddigital\uploadit\assetbundles\uploadit;

use yii\web\AssetBundle;

class UploaditAssetBundle extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->sourcePath = "https://unpkg.com/filepond/dist/";
        $this->depends = [];
        $this->js = ['filepond.js'];
        $this->css = ['filepond.css'];
        parent::init();
    }
}
