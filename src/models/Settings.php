<?php
namespace presseddigital\uploadit\models;

use craft\base\Model;

class Settings extends Model
{
    // Public Properties
    // =========================================================================

    public $includeFilePondLibrary = true;

    // Public Methods
    // =========================================================================

	public function rules(): array
    {
        return [
            ['includeFilePondLibrary', 'boolean'],
            ['includeFilePondLibrary', 'default', 'value' => true],
        ];
    }

}
