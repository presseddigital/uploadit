<?php

namespace presseddigital\uploadit;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use presseddigital\uploadit\models\Settings;
use presseddigital\uploadit\services\Upload as UploadService;
use presseddigital\uploadit\web\twig\Extension;

/**
 * Uploadit plugin
 *
 * @method static Uploadit getInstance()
 * @method Settings getSettings()
 * @author Pressed Digital <hi@pressed.digital>
 * @copyright Pressed Digital
 * @license https://craftcms.github.io/license/ Craft License
 */
class Uploadit extends Plugin
{
    public static $plugin;
    public static $settings;

    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => [
                'upload' => UploadService::class,
            ],
        ];
    }

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;
        self::$settings = $this->getSettings();

        $this->attachEventHandlers();

        Craft::$app->onInit(function() {
            // ...
        });

        Craft::$app->getView()->registerTwigExtension(new Extension());
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('uploadit/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)
    }
}
