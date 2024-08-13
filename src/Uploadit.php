<?php

namespace presseddigital\uploadit;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use presseddigital\uploadit\models\Settings;

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
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        $this->attachEventHandlers();

        // Any code that creates an element query or loads Twig should be deferred until
        // after Craft is fully initialized, to avoid conflicts with other plugins/modules
        Craft::$app->onInit(function() {
            // ...
        });
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
