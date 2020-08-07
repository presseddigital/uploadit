<?php
namespace presseddigital\uploadit\web\twig;

use presseddigital\uploadit\Uploadit;
use presseddigital\uploadit\models\{FieldUploader, VolumeUploader, UserPhotoUploader};

use Craft;
use yii\di\ServiceLocator;

class UploaditVariable extends ServiceLocator
{
    // Properties
    // =========================================================================

    public $plugin;

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();
        $this->plugin = Uploadit::$plugin;
    }

    // Field Uploader
    // =========================================================================

    public function field(array $config = [])
    {
        $uploader = new FieldUploader($config);
        return $uploader->render();
    }

    // Volume Uploader
    // =========================================================================

    public function volume()
    {
        $uploader = new VolumeUploader($config);
        return $uploader->render();
    }

    // User Photo Uploader
    // =========================================================================

    public function userPhoto()
    {
        $uploader = new UserPhotoUploader($config);
        return $uploader->render();
    }

}
