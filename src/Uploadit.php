<?php
namespace presseddigital\uploadit;

use presseddigital\uploadit\models\Settings;
use presseddigital\uploadit\services\Upload as UploadService;
use presseddigital\uploadit\web\twig\Extension;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterUrlRulesEvent;
use yii\base\Event;

class Uploadit extends Plugin
{
    // Static Properties
    // =========================================================================

    public static $plugin;
    public static $settings;
    public static $view;
    public static $variable;

    // Public Properties
    // =========================================================================

    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();
        self::$plugin = $this;
        self::$view = Craft::$app->getView();
        self::$settings = $this->getSettings();

        $this->setComponents([
            'upload' => UploadService::class,
        ]);

        self::$view->registerTwigExtension(new Extension());

        Craft::info(Craft::t('uploadit', '{name} plugin loaded', ['name' => $this->name]), __METHOD__);
    }


    // Protected Methods
    // =========================================================================

    protected function createSettingsModel()
    {
        return new Settings();
    }

}
