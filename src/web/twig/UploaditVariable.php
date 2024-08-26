<?php
namespace presseddigital\uploadit\web\twig;

use presseddigital\uploadit\Uploadit;
use presseddigital\uploadit\models\{FieldUploader, VolumeUploader, AvatarUploader};

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

    public function field(array $config = [])
    {
        return (new FieldUploader($config))->render() ?? '';
    }

    public function volume(array $config = [])
    {
        return (new VolumeUploader($config))->render() ?? '';
    }

    public function avatar(array $config = [])
    {
        return (new AvatarUploader($config))->render() ?? '';
    }

}
