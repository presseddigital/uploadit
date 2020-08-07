<?php
namespace presseddigital\uploadit\helpers;

use Craft;
use craft\db\Query;

class Fields
{
    // Properties
    // =========================================================================

    private static $_fieldsMap;

    // Public Methods
    // =========================================================================

    public static function getFieldsMap()
    {
        self::_buildFieldsMap();
        return self::$_fieldsMap;
    }

    public static function getFieldByHandle(string $handle)
    {
        self::_buildFieldsMap();
        $fieldId = self::$_fieldsMap[$handle] ?? false;
        return $fieldId ? Craft::$app->getFields()->getFieldById($fieldId) : false;
    }

    public static function getFieldIdByHandle(string $handle)
    {
        self::_buildFieldsMap();
        return self::$_fieldsMap[$handle] ?? false;
    }

    // Private Methods
    // =========================================================================

    private static function _buildFieldsMap()
    {
        if (self::$_fieldsMap === null) {

            $fields = (new Query())
                ->select(['id', 'handle', 'context'])
                ->from(['{{%fields}}'])
                ->all();

            $matrixFieldTypes =  (new Query())
                ->select(['matrixblocktypes.id', 'matrixblocktypes.handle', 'fields.handle as fieldHandle'])
                ->from(['{{%matrixblocktypes}} matrixblocktypes'])
                ->orderBy('matrixblocktypes.id')
                ->innerJoin('{{%fields}} fields', '[[fields.id]] = [[matrixblocktypes.fieldId]]')
                ->all();

            $matrixFieldsContext = [];
            foreach ($matrixFieldTypes as $matrixFieldType)
            {
                $matrixFieldsContext['matrixBlockType:'.$matrixFieldType['id']] = $matrixFieldType['fieldHandle'].':'.$matrixFieldType['handle'].':';
            }

            $fieldMap = [];
            foreach ($fields as $field)
            {
                if(array_key_exists($field['context'], $matrixFieldsContext))
                {
                    $handle = $matrixFieldsContext[$field['context']].$field['handle'];
                    $fieldMap[$handle] = $field['id'];
                }
                else
                {
                    $fieldMap[$field['handle']] = $field['id'];
                }
            }

            self::$_fieldsMap = $fieldMap;
        }
    }

}
