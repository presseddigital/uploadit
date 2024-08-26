<?php
namespace presseddigital\uploadit\models;

use presseddigital\uploadit\Uploadit;
use presseddigital\uploadit\base\Uploader;

use Craft;
use craft\base\VolumeInterface;
use craft\models\VolumeFolder;

class VolumeUploader extends Uploader
{
    // Static
    // =========================================================================

    public static function type(): string
    {
        return 'volume';
    }

    // Properties
    // =========================================================================

    private $_folder;

    // Public Methods
    // =========================================================================

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['folder'], 'required', 'message' => Craft::t('uploadit', 'A valid folder or volume is required.')];
        $rules[] = [
            ['folder'],
            function ($attribute)
            {
                if(!$this->folder instanceof VolumeFolder)
                {
                    $this->addError('folder', Craft::t('app', '{attribute} is invalid', ['attribute' => Craft::t('app', 'Folder')]));
                }
            },
        ];
        return $rules;
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'folder';
        return $attributes;
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels['folder'] = Craft::t('app', 'Folder');
        return $labels;
    }

    public function getRequestParams()
    {
        $params = parent::getRequestParams();
        if($this->folder)
        {
            $params['folderId'] = $this->folder->id;
        }
        return $params;
    }

    public function beforeRender()
    {
        $this->allowReorder = false;
    }

    public function getFolder()
    {
        return $this->_folder;
    }

    public function setFolder($folder)
    {
        $this->_folder = false;
        if($folder instanceof VolumeFolder)
        {
            $this->_folder = $folder;
            return;
        }

        if(is_numeric($folder))
        {
            $this->_folder = Craft::$app->getAssets()->getFolderById((int)$folder);
            return;
        }

        if($folder instanceof VolumeInterface)
        {
            $folderId = Craft::$app->getVolumes()->ensureTopFolder($folder);
            $this->setFolder($folderId);
            return;
        }

        $volume = $folder['volume'] ?? false;
        if($volume)
        {
            switch (true)
            {
                case is_numeric($volume):
                    $volume = Craft::$app->getVolumes()->getVolumeById((int)$volume);
                    break;
                case is_string($volume):
                    $volume = Craft::$app->getVolumes()->getVolumeByHandle($volume);
                    break;
            }

            if($volume instanceof VolumeInterface)
            {
                $path = $folder['path'] ?? false;
                if($path)
                {
                    $this->setFolder(Craft::$app->getAssets()->ensureFolderByFullPathAndVolume($path, $volume, false));
                    return;
                }
                else
                {
                    $this->setFolder($volume);
                    return;
                }
            }
        }
    }
}
