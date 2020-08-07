<?php
namespace presseddigital\uploadit\models;

use presseddigital\uploadit\Uploadit;
use presseddigital\uploadit\base\Uploader;
use presseddigital\uploadit\helpers\{Fields, Assets};

use Craft;
use craft\base\ElementInterface;
use craft\base\FieldInterface;

class FieldUploader extends Uploader
{

    // Static
    // =========================================================================

    public static function type(): string
    {
        return self::TYPE_FIELD;
    }

    // Public
    // =========================================================================

    public $name;
    public $field;
    public $element;
    public $saveOnUpload = false;

    // Public Methods
    // =========================================================================

    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['name'], 'required'];
        $rules[] = [['target'], 'required', 'message' => Craft::t('uploadit', 'A valid field and element must be set.')];
        return $rules;
    }

    public function getJavascriptProperties(): array
    {
        $variables = parent::getJavascriptProperties();
        $variables[] = 'name';
        $variables[] = 'saveOnUpload';
        return $variables;
    }

    public function render()
    {
        // Get Element
        $element = $this->element;
        if($element && !$element instanceof ElementInterface)
        {
            $element = Craft::$app->getElements()->getElementById((int) $this->element);
        }

        // Get Field
        $field = $this->field;
        if($field && !$this->field instanceof FieldInterface)
        {
            if(is_numeric($field))
            {
                $field = Craft::$app->getFields()->getFieldById($field);
            }
            else
            {
                $field = Fields::getFieldByHandle($this->field);
            }
        }

        // Field Setup
        if($field)
        {
            $this->limit = $field->limit ? $field->limit : null;
            $this->allowedFileExtensions = Assets::getAllowedFileExtensionsByFieldKinds($field->allowedKinds);
            $this->setUploadRequestParams([
                'fieldId' => $field->id,
                'elementId' => $element->id ?? null
            ]);
        }
        else
        {
            $this->addError('field', Craft::t('uploadit', 'Could not locate your field.'));
        }

        return parent::render();
    }

}
