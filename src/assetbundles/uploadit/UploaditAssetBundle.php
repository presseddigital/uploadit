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
            'filepond-plugin-image-crop/dist/filepond-plugin-image-crop.min.js',
            'filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.min.js',
            'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js',
            'filepond-plugin-image-resize/dist/filepond-plugin-image-resize.min.js',
            'filepond-plugin-image-validate-size/dist/filepond-plugin-image-validate-size.min.js',
            'filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js',
            'filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js',
            'filepond-plugin-file-poster/dist/filepond-plugin-file-poster.min.js',
            'filepond/dist/filepond.min.js'
        ];

        $this->jsOptions = [
            'position' => View::POS_HEAD
        ];

        $this->css = [
            'filepond/dist/filepond.min.css',
            'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css',
            'filepond-plugin-file-poster/dist/filepond-plugin-file-poster.min.css',
        ];

        parent::init();
    }

    public function registerAssetFiles($view)
    {
        parent::registerAssetFiles($view);
        $view->registerJs('FilePond.registerPlugin(
            FilePondPluginImageResize,
            FilePondPluginImageCrop,
            FilePondPluginImageExifOrientation,
            FilePondPluginImageValidateSize,
            FilePondPluginImagePreview,
            FilePondPluginFileValidateSize,
            FilePondPluginFileValidateType,
            FilePondPluginFilePoster
        );', View::POS_HEAD);
    }
}
