<?php

namespace presseddigital\uploadit\models;

use Craft;
use craft\base\Model;

/**
 * Uploadit settings
 */
class Settings extends Model
{
    public $includeFilePondLibrary = true;

	public function rules(): array
    {
        return [
            ['includeFilePondLibrary', 'boolean'],
            ['includeFilePondLibrary', 'default', 'value' => true],
        ];
    }
}
