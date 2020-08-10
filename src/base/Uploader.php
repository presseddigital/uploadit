<?php
namespace presseddigital\uploadit\base;

use presseddigital\uploadit\Uploadit;
use presseddigital\uploadit\base\UploaderInterface;
use presseddigital\uploadit\assetbundles\uploadit\UploaditAssetBundle;

use Craft;
use craft\web\View;
use craft\base\Model;
use craft\helpers\{Json, Template, UrlHelper};
use craft\elements\db\ElementQueryInterface;

abstract class Uploader extends Model implements UploaderInterface
{
    // Constants
    // =========================================================================

    const TYPE_VOLUME = 'volume';
    const TYPE_FIELD = 'field';
    const TYPE_USER_PHOTO = 'userPhoto';

    // Private
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

    private $_defaultJavascriptVariables;
    private $_uploadRequestParams;

    // Public
    // =========================================================================

    public $id;
    public $name;

    public $multiple = false;

    // Assets
    public $assets;

    // Target
    public $target;

    // Settings
    public $enableDropToUpload = true;
    public $enableReorder = true;
    public $enableRemove = true;

    // Styles, Layout & Preview
    public $selectText;
    public $dropText;

    // Asset
    public $limit;
    public $maxSize;
    public $allowedFileExtensions;



    // FilePond Options


    // Public Methods
    // =========================================================================

    public function __construct(array $config = [])
    {

        $generalConfig = Craft::$app->getConfig()->getGeneral();

        // Defualt Settings
        $this->id = uniqid('uploadit');
        $this->selectText = Craft::t('uploadit', 'Select files');
        $this->dropText = Craft::t('uploadit', 'drop files here');
        $this->maxSize = $generalConfig->maxUploadFileSize;
        $this->allowedFileExtensions = $generalConfig->allowedFileExtensions;

        // Default Javascript Variables
        $this->_defaultJavascriptVariables = [
            'csrfTokenName' => $generalConfig->csrfTokenName,
            'csrfTokenValue' => Craft::$app->getRequest()->getCsrfToken(),
        ];

        parent::__construct($config);
    }

    public function getFilePondOptions()
    {
        $options = [
            'name' => 'assets-upload',
            'maxFiles' =>  null,
            'allowBrowse' => true,
            'allowReorder' => true,
            'dropValidation' => true,
            'instantUpload' => true,
        ];

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
                            'poster' => $asset->kind == 'image' ? $asset->getUrl() : null
                        ]
                    ]
                ];
            }, $assets);

            // [
            //     [
            //         // the server file reference
            //         'source' => '12345',

            //         // set type to local to indicate an already uploaded file
            //         'options' => [
            //             'type' => 'local',

            //             // mock file information
            //             'file' => [
            //                 'name' => 'my-file.png',
            //                 'size' => 3001025,
            //                 'type' => 'image/png'
            //             ],
            //             'metadata' => [
            //                 'poster' => 'https://beta.findarace.test/index.php?p=admin/actions/assets/thumb&uid=d0f456f7-8c95-445b-8376-b86ea58933d6&width=616&height=380&v=1597041760'
            //             ]
            //         ]
            //     ]
            // ];
        }

        return Json::encode($options, JSON_NUMERIC_CHECK);
    }

    public function setUploadRequestParams(array $params = null)
    {
        $this->_uploadRequestParams = $params;
    }

    public function getUploadRequestParams()
    {
        return $this->_uploadRequestParams;
    }

    public function render()
    {
        $config = Craft::$app->getConfig()->getGeneral();
        $view = Craft::$app->getView();

        $view->registerAssetBundle(UploaditAssetBundle::class);

        $siteUrl = UrlHelper::siteUrl();
        $formData = '';
        $uploadRequestParams = $this->getUploadRequestParams();
        $uploadRequestParams['action'] = self::action();
        $uploadRequestParams[$config->csrfTokenName] = Craft::$app->getRequest()->getCsrfToken();
        foreach ($uploadRequestParams as $param => $value)
        {
            $formData .= "formData.append('{$param}', '{$value}');";
        }

        $js = <<<JS
var {$this->id}Uploader = FilePond.create(document.getElementById('{$this->id}'), {$this->getFilePondOptions()});
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

    public function rules()
    {
        // IDEA: Should target use this for validation: https://www.yiiframework.com/doc/guide/2.0/en/tutorial-core-validators#filter

        $rules = parent::rules();
        $rules[] = [['maxSize'], 'integer', 'max' => $this->_defaultMaxUploadFileSize, 'message' => Craft::t('uploadit', 'Max file can\'t be greater than the global setting maxUploadFileSize')];
        return $rules;
    }

    public function beforeValidate()
    {
        $this->_checkTransformExists();
        $this->setTarget();
    }

    public function getJavascriptProperties(): array
    {
        return [
            'id',
            'target',
            'layout',
            'view',
            'limit',
            'maxSize',
            'transform',
            'allowedFileExtensions',
            'enableDropToUpload',
            'enableReorder',
            'enableRemove'
        ];
    }

    public function setTarget(): bool
    {
        return null;
    }

    // Protected Methods
    // =========================================================================

    protected function getJavascriptVariables(bool $encode = true)
    {
        $settings = $this->_defaultJavascriptVariables;
        $settings['type'] = static::type();
        foreach ($this->getJavascriptProperties() as $property)
        {
            $settings[$property] = $this->$property ?? null;
        }

        return $encode ? Json::encode($settings) : $settings;
    }

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
