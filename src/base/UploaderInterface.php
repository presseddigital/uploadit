<?php
namespace presseddigital\uploadit\base;

interface UploaderInterface
{
    // Static
    // =========================================================================

    public static function type(): string;
    public static function action(): string;

}
