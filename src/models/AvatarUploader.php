<?php
namespace presseddigital\uploadit\models;

use presseddigital\uploadit\Uploadit;
use presseddigital\uploadit\base\Uploader;
use presseddigital\uploadit\helpers\{Fields, Assets};

use Craft;
use craft\base\ElementInterface;
use craft\base\FieldInterface;

class AvatarUploader extends Uploader
{
    // Constants
    // =========================================================================

    const DEFAULT_SIZE = 100;

    // Static
    // =========================================================================

    public static function type(): string
    {
        return 'avatar';
    }

    public static function actionProcess(): string
    {
        return 'uploadit/upload/avatar';
    }

    public static function actionRemove(): string
    {
        return 'uploadit/upload/avatar';
    }

    // Public
    // =========================================================================

    public $round = false;
    public $user;


    // public $default;
    // public $width;
    // public $height;
    // public $photo;

    // public $imageClasses;


    // Public Methods
    // =========================================================================

    // public function __construct(array $attributes = [])
    // {
    //     parent::__construct();

    //     $this->enableRemove = true;
    //     $this->selectText = Craft::t('uploadit', 'Edit');

    //     // Populate
    //     $this->setAttributes($attributes, false);

    //     // Force
    //     $this->limit = 1;
    //     $this->allowedFileExtensions = Upload::getAllowedFileExtensionsByFieldKinds(['image']);
    //     $this->enableReorder = false;
    //     $this->setTarget();

    // }

    public function rules()
    {
        $rules = parent::rules();
        return $rules;
    }

    // To Make Square
    // .filepond--root[data-style-panel-layout~=circle],
    // .filepond--root[data-style-panel-layout~=circle] .filepond--image-preview-wrapper { border-radius: 0; }

    public function beforeRender()
    {
        $this->layout = 'circle';
        $this->multiple = false;
        $this->limit = 1;
        $this->allowedFileExtensions = Assets::getAllowedFileExtensionsByFieldKinds(['image']);
        $this->imagePreview = true;
        $this->allowReorder = false;
        $this->allowRemove = true;
    }

}
