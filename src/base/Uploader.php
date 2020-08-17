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

    public static function action(): string
    {
        return 'uploadit/upload';
    }

    // Private
    // =========================================================================

    private $_requestParams;
    private $_craftMaxUploadSize;
    private $_maxSize;
    private $_transform;

    // Public
    // =========================================================================

    public $id;
    public $name;

    public $multiple = false;

    public $imagePreview = true;
    public $allowReorder = true;
    public $allowRemove = true;

    public $limit;
    public $allowedFileExtensions;

    public $allowDrop = true;
    public $allowBrowse = true;

    public $dropText;
    public $orText;
    public $browseText;

    public $layout;

    public $assets;
    public $options = [];

    // Public Methods
    // =========================================================================

    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

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
        $rules[] = [['browseText', 'dropText', 'orText'], 'string'];
        $rules[] = [['allowBrowse', 'allowDrop'], 'boolean'];
        $rules[] = [['allowBrowse', 'allowDrop'], 'default', 'value' => true];
        $rules[] = [['maxSize'], 'integer'];
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

        // Transform
        // TODO: @sam - Get Transform, could be string, array or Transform
        //            - Use to populate view for the transform and deliver the asset to the file poster plugin

        // Options
        $options = [
            // FilePond
            'maxFiles' =>  $this->limit,
            'allowBrowse' => $this->allowBrowse,
            'allowReorder' => $this->allowReorder,
            'allowDrop' => $this->allowDrop,
            'allowPaste' => true,
            'allowMultiple' => true,
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
            'fileValidateTypeLabelExpectedTypesMap' => array_flip(Assets::getExtensionMimeTypeMap($this->allowedFileExtensions)),
            // FileSizeValidation
            'allowFileSizeValidation' => true,
            'maxFileSize' => ($this->maxSize / 1024).'KB',
            // FilePoster
            'allowFilePoster' => $this->imagePreview,
            // ImagePreview
            'allowImagePreview' => $this->imagePreview,
        ];

        if($this->transform)
        {
            $options['allowImageResize'] = true;
            $options['imageResizeTargetWidth'] = $this->transform->width;
            $options['imageResizeTargetHeight'] = $this->transform->height;
            $options['imageResizeMode'] = $this->transform->mode == 'stretch' ? 'cover' : 'contain';
            $options['imageResizeUpscale'] = true;
        }

        if($this->assets)
        {
            $assets = $this->assets instanceof ElementQueryInterface ? $this->assets->all() : $this->assets;

            $options['files'] = array_map(function($asset) {
                return [
                    'source' => $asset->id,
                    'options' => [
                        'type' => 'local',
                        'file' => [
                            'name' => $asset->getFilename(),
                            'size' => $asset->size,
                            'type' => $asset->getMimeType()
                        ],
                        'metadata' => [
                            'poster' => $asset->kind == 'image' ? $asset->getUrl($this->transform) : null
                        ]
                    ]
                ];
            }, $assets);
        }

        if($this->options)
        {
            $options = array_merge($options, $this->options);
        }

        return Json::encode($options, JSON_NUMERIC_CHECK);
    }

    public function setRequestParams(array $params = null)
    {
        $this->_requestParams = $params;
    }

    public function getRequestParams()
    {
        return $this->_requestParams;
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

    public function render()
    {
        $this->validate();

        $config = Craft::$app->getConfig()->getGeneral();
        $view = Craft::$app->getView();

        $view->registerAssetBundle(UploaditAssetBundle::class);

        $siteUrl = UrlHelper::siteUrl();
        $formData = '';
        $uploadRequestParams = $this->getRequestParams();
        $uploadRequestParams['action'] = self::action();
        $uploadRequestParams[$config->csrfTokenName] = Craft::$app->getRequest()->getCsrfToken();
        foreach ($uploadRequestParams as $param => $value)
        {
            $formData .= "formData.append('{$param}', '{$value}');";
        }

        $js = <<<JS
var {$this->id}Element = document.getElementById('{$this->id}');
var {$this->id}Uploader = FilePond.create({$this->id}Element, {$this->getFilePondOptions()});
{$this->id}Uploader.on('init', function() {
    {$this->id}Uploader.element.parentElement.classList.remove('hidden');
});

{$this->id}Uploader.setOptions({
    server: {
        url: '{$siteUrl}',
        process: {
            url: '/',
            method: 'POST',
            headers: {},
            withCredentials: false,
            ondata: (formData) => {
                formData.getAll('{$this->name}').forEach(function(data) {
                    if(data instanceof File) {
                        formData.set('assets-upload', data);
                    }
                });
                formData.delete('{$this->name}');
                {$formData}
                return formData;
            }
        },
        revert: null,
        restore: null,
        load: null,
        fetch: null
    },
});
JS;

        $view->registerJs($js, View::POS_END);
        // $view->registerCss($this->getCustomCss());

        $templateMode = $view->getTemplateMode();
        $view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $html = $view->renderTemplate('uploadit/uploader', [
            'uploader' => $this
        ]);
        $view->setTemplateMode($templateMode);

        return Template::raw($html);
    }

    // Protected Methods
    // =========================================================================

    // Private Methods
    // =========================================================================

    // private function _checkTransformExists()
    // {
    //     if(is_string($this->transform) && !empty($this->transform))
    //     {
    //         if(!Craft::$app->getAssetTransforms()->getTransformByHandle($this->transform))
    //         {
    //             $this->addError('transform', Craft::t('uploadit', 'Asset transform does not exist'));
    //             return false;
    //         }
    //     }
    //     return true;
    // }
}
