<?php
namespace presseddigital\uploadit\web\twig;

use presseddigital\uploadit\Uploadit;

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

    public function field()
    {
        Craft::dd('OUTPUT FIELD UPLOADER');
        return $this->plugin;
    }

    // Volume Uploader
    // =========================================================================

    public function volume()
    {
        Craft::dd('OUTPUT VOLUME UPLOADER');
        return $this->plugin;
    }

    // User Photo Uploader
    // =========================================================================

    public function userPhoto()
    {
        Craft::dd('OUTPUT USER PHOTO UPLOADER');
        return $this->plugin;
    }

}
