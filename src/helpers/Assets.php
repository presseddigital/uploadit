<?php
namespace presseddigital\uploadit\helpers;

use Craft;

class Assets extends craft\helpers\Assets
{
    // Field Map
    // =========================================================================

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
