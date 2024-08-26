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
        return 'field';
    }

    // Properties
    // =========================================================================

    private $_field;
    private $_element;

    public $name;
    // public $saveElementOnUpload = false;

    // Public Methods
    // =========================================================================

    public function getField()
    {
        return $this->_field;
    }

    public function setField($field)
    {
        if($field instanceof FieldInterface)
        {
            $this->_field = $field;
            return;
        }

        if(is_numeric($field))
        {
            $this->_field = Craft::$app->getFields()->getFieldById($field);
            return;
        }

        if(is_string($field))
        {
            $this->_field = Fields::getFieldByHandle($field);
            return;
        }

        $this->_field = false;
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function setElement($element)
    {
        if($element instanceof ElementInterface)
        {
            $this->_element = $element;
            return;
        }

        if(is_numeric($element))
        {
            $this->_element = Craft::$app->getElements()->getElementById((int) $element);
            return;
        }

        $this->_element = false;
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['name'], 'string'];
        $rules[] = [['name', 'field', 'element'], 'required'];
        $rules[] = [
            ['field'],
            function ($attribute)
            {
                if(!$this->field instanceof FieldInterface)
                {
                    $this->addError('field', Craft::t('app', '{attribute} is invalid', ['attribute' => Craft::t('app', 'Field')]));
                }
            },
        ];
        $rules[] = [
            ['element'],
            function ($attribute)
            {
                if(!$this->element instanceof ElementInterface)
                {
                    $this->addError('element', Craft::t('app', '{attribute} is invalid', ['attribute' => Craft::t('app', 'Element')]));
                }
            },
        ];
        return $rules;
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'field';
        $attributes[] = 'element';
        return $attributes;
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels['field'] = Craft::t('app', 'Field');
        $labels['element'] = Craft::t('app', 'Element');
        $labels['name'] = Craft::t('app', 'Name');
        // $labels['saveElementOnUpload'] = Craft::t('uploadit', 'Save Element On Upload');
        return $labels;
    }

    public function getRequestParams()
    {
        $params = parent::getRequestParams();
        if($this->field && $this->element)
        {
            $params['fieldId'] = $this->field->id;
            $params['elementId'] = $this->element->id;
        }
        // $params['save'] = $this->saveElementOnUpload;
        return $params;
    }

    public function beforeRender()
    {
        if($this->field && $this->element)
        {
            $this->limit = $this->field->limit ? $this->field->limit : null;
            $this->allowedFileExtensions = Assets::getAllowedFileExtensionsByFieldKinds($this->field->allowedKinds);
        }
    }

}
