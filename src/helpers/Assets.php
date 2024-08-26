<?php
namespace presseddigital\uploadit\helpers;

use Craft;
use Mimey\MimeTypes;

class Assets extends craft\helpers\Assets
{

    // Private
    // =========================================================================

    private static $_extensionMimeTypeMap;

    // Public
    // =========================================================================


    public static function getExtensionMimeTypeMap(array $extensions = null)
    {
        if($extensions)
        {
            $map = self::getExtensionMimeTypeMap();
            return array_filter($map, function($extension) use ($extensions) {
                return in_array($extension, $extensions);
            }, ARRAY_FILTER_USE_KEY);
        }

        if(self::$_extensionMimeTypeMap !== null)
        {
            return self::$_extensionMimeTypeMap;
        }

        $extensions = Craft::$app->getConfig()->getGeneral()->allowedFileExtensions;
        $mimes = new MimeTypes;
        $map = [];
        foreach ($extensions as $extension)
        {
            if($mimeType = $mimes->getMimeType($extension))
            {
                $map[$extension] = $mimeType;
            }

        }
        return self::$_extensionMimeTypeMap = $map;
    }

    public static function getExtensionsAsMimeTypes(array $extensions = [])
    {
        return array_values(self::getExtensionMimeTypeMap($extensions));
    }

    public static function getAllowedFileExtensionsByFieldKinds(array $kinds = null)
    {
        if(!$kinds)
        {
            return Craft::$app->getConfig()->getGeneral()->allowedFileExtensions;
        }

        $fileKinds = \craft\helpers\Assets::getFileKinds();

        $allowedFileExtensions = [];
        if($kinds)
        {
            foreach($kinds as $kind)
            {
                if(array_key_exists($kind, $fileKinds))
                {
                    $allowedFileExtensions = array_merge($allowedFileExtensions, $fileKinds[$kind]['extensions']);
                }
            }
        }

        $allowedFileExtensionsFromConfig = Craft::$app->getConfig()->getGeneral()->allowedFileExtensions;

        $vaidatedAllowedFileExtensions = [];
        foreach ($allowedFileExtensions as $allowedFileExtension)
        {
            if(in_array($allowedFileExtension, $allowedFileExtensionsFromConfig))
            {
                $vaidatedAllowedFileExtensions[] = $allowedFileExtension;
            }
        }
        return $vaidatedAllowedFileExtensions;
    }

}
