<?php
namespace presseddigital\uploadit\assetbundles\uploadit;

use craft\web\View;
use yii\web\AssetBundle;

class UploaditAssetBundle extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->baseUrl = 'https://unpkg.com/';
        $this->js = [
            'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js',
            'filepond-plugin-image-validate-size/dist/filepond-plugin-image-validate-size.min.js',
            'filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js',
            'filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js',
            'filepond/dist/filepond.min.js'
        ];

        $this->jsOptions = [
            'position' => View::POS_HEAD
        ];

        $this->css = [
            'filepond/dist/filepond.min.css',
            'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css',
        ];

        parent::init();
    }

    public function registerAssetFiles($view)
    {
        parent::registerAssetFiles($view);
        $view->registerJs('FilePond.registerPlugin(
            FilePondPluginImagePreview,
            FilePondPluginFileValidateSize,
            FilePondPluginFileValidateType,
            FilePondPluginImageValidateSize
        );', View::POS_HEAD);
        // $view->registerCss('[x-cloak] { display:none; }');
    }
}
