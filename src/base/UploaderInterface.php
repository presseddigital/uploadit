<?php
namespace presseddigital\uploadit\base;

interface UploaderInterface
{
    public static function type(): string;
    public static function actionProcess(): string;
    public static function actionRemove(): string;
}
