<?php
namespace presseddigital\uploadit\variables;

use presseddigital\uploadit\models\Uploader; // DEPRICIATE

use presseddigital\uploadit\Uploadit;
use presseddigital\uploadit\assetbundles\uploadit\UploaditAssetBundle;
use presseddigital\uploadit\models\VolumeUploader;
use presseddigital\uploadit\models\FieldUploader;
use presseddigital\uploadit\models\UserPhotoUploader;

use Craft;
use craft\web\View;
use craft\helpers\Template as TemplateHelper;
use craft\helpers\Json as JsonHelper;

class UploaditVariable
{
    // Public Methods
    // =========================================================================

    public function volumeUploader($attributes = [])
    {
        return $this->_renderUploader(VolumeUploader::class, $attributes);
    }

    public function fieldUploader($attributes = [])
    {
        return $this->_renderUploader(FieldUploader::class, $attributes);
    }

    public function userPhotoUploader($attributes = [])
    {
        return $this->_renderUploader(UserPhotoUploader::class, $attributes);
    }

    // Private Methods
    // =========================================================================

    public function _renderUploader($type, $attributes = [])
    {
        try{
            $uploader = new $type($attributes);
        } catch(\Throwable $exception) {
            $uploader = false;
        }

        if(!$uploader)
        {
            return TemplateHelper::raw('<p>Invalid Uploder!</p>');
        }

        return TemplateHelper::raw($uploader->render());
    }

}
