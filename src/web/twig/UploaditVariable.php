<?php
namespace presseddigital\uploadit\web\twig;

use presseddigital\uploadit\Uploadit;

use presseddigital\uploadit\assetbundles\uploadit\UploaditAssetBundle;


use Craft;
use craft\web\View;
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
        $view = Craft::$app->getView();
        $view->registerAssetBundle(UploaditAssetBundle::class);

        $templateMode = $view->getTemplateMode();
        $view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $html = $view->renderTemplate('uploadit/uploader', [
            'uploader' => []
        ]);
        $view->setTemplateMode($templateMode);

        return $html;
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
