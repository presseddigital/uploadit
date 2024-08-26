<?php
namespace presseddigital\uploadit\base;

use presseddigital\uploadit\Uploadit;
use presseddigital\uploadit\base\UploaderInterface;
use presseddigital\uploadit\helpers\Assets;
use presseddigital\uploadit\assetbundles\uploadit\UploaditAssetBundle;

use Craft;
use craft\web\View;
use craft\base\Model;
use craft\helpers\{Json, Template, UrlHelper};
use craft\elements\db\ElementQueryInterface;

abstract class Uploader extends Model implements UploaderInterface
{
    // Static
    // =========================================================================

    public static function type(): string
    {
        return null;
    }

    public static function actionProcess(): string
    {
        return 'uploadit/upload/process';
    }

    public static function actionRemove(): string
    {
        return 'uploadit/upload/remove';
    }

    // Private
    // =========================================================================

    private $_craftMaxUploadSize;
    private $_maxSize;
    private $_transform;

    // Public
    // =========================================================================

    public $id;
    public $name = 'assets-upload';

    public $multiple = false;

    public $imagePreview = true;
    public $imagePreviewHeight = 200;
    public $allowReorder = true;
    public $allowRemove = true;

    public $enableDeleteAssets = false;

    public $limit;
    public $allowedFileExtensions;

    public $allowDrop = true;
    public $allowBrowse = true;

    public $dropText;
    public $orText;
    public $browseText;

    public $layout;

    public $assets;
    public $options;

    // Public Methods
    // =========================================================================

    public function init()
    {
        $generalConfig = Craft::$app->getConfig()->getGeneral();
        $this->_craftMaxUploadSize = $generalConfig->maxUploadFileSize;

        if(!$this->id) {
            $this->id = uniqid('uploadit');
        }

        if(!$this->allowedFileExtensions) {
            $this->allowedFileExtensions = $generalConfig->allowedFileExtensions;
        }

        if(!$this->maxSize) {
            $this->maxSize = $this->_craftMaxUploadSize;
        }

        if(!$this->dropText) {
            $this->dropText = Craft::t('uploadit', 'Drop files here');
        }

        if(!$this->orText) {
            $this->orText = Craft::t('uploadit', 'or');
        }

        if(!$this->browseText) {
            $this->browseText = Craft::t('uploadit', 'browse');
        }
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['browseText', 'dropText', 'orText', 'name'], 'string'];
        $rules[] = [['name'], 'default', 'value' => 'assets-upload'];
        $rules[] = [['allowBrowse', 'allowDrop', 'imagePreview', 'allowRemove', 'enableDeleteAssets'], 'boolean'];
        $rules[] = [['allowBrowse', 'allowDrop', 'imagePreview', 'allowRemove'], 'default', 'value' => true];
        $rules[] = [['enableDeleteAssets'], 'default', 'value' => false];
        $rules[] = [['maxSize'], 'integer'];
        $rules[] = [['imagePreviewHeight'], 'integer', 'min' => 44];
        $rules[] = [['imagePreviewHeight'], 'default', 'value' => 200];
        $rules[] = [['layout'], 'in', 'range' => ['integrated', 'compact', 'circle']];
        return $rules;
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'maxSize';
        $attributes[] = 'requestParams';
        $attributes[] = 'transform';
        return $attributes;
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels['maxSize'] = Craft::t('uploadit', 'Max Size');
        $labels['requestParams'] = Craft::t('uploadit', 'Request Params');
        $labels['transform'] = Craft::t('app', 'Transform');
        return $labels;
    }


    public function getFilePondOptions()
    {
        // Label
        $labelIdle = $this->allowDrop ? $this->dropText : '';
        if($this->allowDrop && $this->allowBrowse)
        {
            $labelIdle .= ' '.$this->orText.' ';
        }
        if($this->allowBrowse)
        {
            $labelIdle .= ' <span class="filepond--label-action">'.$this->browseText.'</span>';
        }

        // Options
        $options = [
            // FilePond
            'name' =>  $this->name,
            'maxFiles' =>  $this->limit,
            'allowBrowse' => $this->allowBrowse,
            'allowReorder' => $this->allowReorder,
            'allowDrop' => $this->allowDrop,
            'allowPaste' => true,
            'allowMultiple' => $this->multiple,
            'allowReplace' => true,
            'allowRevert' => $this->allowRemove,
            'itemInsertLocation' => 'after',
            'dropValidation' => false,
            'instantUpload' => true,
            'labelIdle' => $labelIdle,
            // Style
            'stylePanelLayout' => $this->layout,
            // FileTypeValidation
            'acceptedFileTypes' => Assets::getExtensionsAsMimeTypes($this->allowedFileExtensions),
            'labelFileTypeNotAllowed' => Craft::t('uploadit', 'File type invalid'),
            'fileValidateTypeLabelExpectedTypes' => Craft::t('uploadit', 'Expects {allTypes}'),
            'fileValidateTypeLabelExpectedTypesMap' => array_flip(Assets::getExtensionMimeTypeMap($this->allowedFileExtensions)),
            // FileSizeValidation
            'allowFileSizeValidation' => true,
            'maxFileSize' => ($this->maxSize / 1024).'KB',
            // FilePoster
            'allowFilePoster' => $this->imagePreview,
            'filePosterHeight' => $this->imagePreviewHeight,
            // ImagePreview
            'allowImagePreview' => $this->imagePreview,
            'imagePreviewHeight' => $this->imagePreviewHeight,
        ];

        // TODO: @sam - Fix Support For Transforms
        //
        // if($this->transform)
        // {
        //     if($this->transform->mode == 'crop')
        //     {
        //         $options['allowImageCrop'] = true;
        //         $options['imageCropAspectRatio'] = ($this->transform->width ?? $this->transform->height ?? 1).':'.($this->transform->height ?? $this->transform->width ?? 1);
        //     }
        //     else
        //     {
        //         $options['allowImageResize'] = true;
        //         $options['imageResizeTargetWidth'] = $this->transform->width;
        //         $options['imageResizeTargetHeight'] = $this->transform->height;
        //         $options['imageResizeMode'] = $this->transform->mode == 'stretch' ? 'cover' : 'contain';
        //         $options['imageResizeUpscale'] = false;
        //     }
        // }

        if($this->assets)
        {
            $assets = $this->assets instanceof ElementQueryInterface ? $this->assets->all() : $this->assets;

            $options['files'] = array_map(function($asset) {

                $file = [
                    'source' => $asset->id,
                    'options' => [
                        'type' => 'local',
                        'file' => [
                            'name' => $asset->getFilename(),
                            'size' => $asset->size,
                            'type' => $asset->getMimeType()
                        ],
                        'metadata' => []
                    ]
                ];

                // TODO: @sam - Update to support transforms or alternative $asset->getUrl($this->transform)
                if($this->imagePreview && $asset->kind == 'image')
                {
                    $file['options']['metadata']['poster'] = $asset->getUrl();
                }
                return $file;

            }, $assets);
        }

        if($this->options)
        {
            $options = array_merge($options, $this->options);
        }

        return Json::encode($options, JSON_NUMERIC_CHECK);
    }

    public function getRequestParams()
    {
        $config = Craft::$app->getConfig()->getGeneral();
        return [
            $config->csrfTokenName => Craft::$app->getRequest()->getCsrfToken()
        ];
    }

    public function setMaxSize(int $size = null)
    {
        if(!$size)
        {
            $this->_maxSize = $this->_craftMaxUploadSize;
        }
        $this->_maxSize = min((int) $size, $this->_craftMaxUploadSize);
    }

    public function getMaxSize()
    {
        return $this->_maxSize;
    }

    public function setTransform($transform)
    {
        try
        {
            $this->_transform = Craft::$app->getAssetTransforms()->normalizeTransform($transform);
        }
        catch(\Exception $e)
        {
            $this->_transform = null;
        }
    }

    public function getTransform()
    {
        return $this->_transform;
    }

    public function beforeRender()
    {
        return null;
    }

    public function render()
    {
        $this->validate();
        $this->beforeRender();

        $siteUrl = UrlHelper::siteUrl();

        $view = Craft::$app->getView();
        $view->registerAssetBundle(UploaditAssetBundle::class);

        $js = <<<JS
var {$this->id}Element = document.getElementById('{$this->id}');
var {$this->id}Uploader = FilePond.create({$this->id}Element, {$this->getFilePondOptions()});
{$this->id}Uploader.on('init', function() { {$this->id}Uploader.element.parentElement.classList.remove('hidden'); });
{$this->id}Uploader.setOptions({
    server: {
        url: '{$siteUrl}',
        process: {$this->_getServerProcessJs()},
        remove: {$this->_getServerRemoveJs()},
        revert: null,
        restore: null,
        load: null,
        fetch: null
    },
});
JS;

        $view->registerJs($js, View::POS_END);

        $templateMode = $view->getTemplateMode();
        $view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $html = $view->renderTemplate('uploadit/uploader', [
            'uploader' => $this
        ]);
        $view->setTemplateMode($templateMode);

        return Template::raw($html);
    }

    // Private Methods
    // =========================================================================

    private function _getServerProcessJs()
    {
        $action = self::actionProcess();
        $formData = '';
        foreach ($this->getRequestParams() as $param => $value)
        {
            $formData .= "formData.append('{$param}', '{$value}');";
        }

        return <<<JS
{
    url: '/',
    method: 'POST',
    headers: {},
    withCredentials: false,
    ondata: (formData) => {

        var name = '{$this->name}';
        if(name != 'assets-upload') {
            formData.getAll(name).forEach(function(data) {
                console.log(data);
                if(data instanceof File) {
                    formData.set('assets-upload', data);
                }
            });
            formData.delete(name);
        }
        formData.append('action', '{$action}');
        {$formData}
        return formData;
    }
}
JS;
    }

    private function _getServerRemoveJs()
    {
        $action = self::actionRemove();
        if(!$action || !$this->enableDeleteAssets)
        {
            return 'null';
        }

        return <<<JS
function (source, load, error) {

    // When deleting from field, should we check if in use anywhere else to allow delete?

    // Should somehow send `source` to server so server can remove the file with this source
    console.log('LETS REMOVE ', source);
    if(true)
    {
        error('Asset could not be removed at source');
    }
    else
    {
        load();
    }
}
JS;
    }

}
