<?php
namespace presseddigital\uploadit\controllers;

use presseddigital\uploadit\Uploadit;
use presseddigital\uploadit\helpers\Upload;

use Craft;
use craft\web\Controller;
use craft\web\UploadedFile;
use craft\controllers\AssetsController;
use craft\helpers\Assets;
use craft\helpers\FileHelper;
use craft\elements\Asset;

class UploadController extends Controller
{
    // Protected
    // =========================================================================

    protected $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    public function actionProcess()
    {
        // AssetsController - actionSaveAsset()
        $response = Craft::$app->runAction('assets/upload');

        if($response->data['error'] ?? false)
        {
            return $this->asErrorJson($response->data['error']);
        }

        return $this->asJson($response->data['assetId']);
    }

    public function actionRemove()
    {
        Craft::dd('Setup Remove Endpoint');

        // AssetsController - actionSaveAsset()
        // $response = Craft::$app->runAction('assets/upload');

        // if($response->data['error'] ?? false)
        // {
        //     return $this->asErrorJson($response->data['error']);
        // }

        // return $this->asJson($response->data['assetId']);
    }

    public function actionUserPhoto()
    {
        $this->requireAcceptsJson();
        $this->requireLogin();

        $request = Craft::$app->getRequest();
        $transform = $request->getParam('transform', '');

        if (($file = UploadedFile::getInstanceByName('photo')) === null)
        {
            return $this->asErrorJson(Craft::t('uploadit', 'User photo is required.'));
        }
        try {
            if ($file->getHasError())
            {
                return $this->asErrorJson($file->error);
            }

            $users = Craft::$app->getUsers();
            $user = Craft::$app->getUser()->getIdentity();

            // Move to our own temp location
            $fileLocation = Assets::tempFilePath($file->getExtension());
            move_uploaded_file($file->tempName, $fileLocation);
            $users->saveUserPhoto($fileLocation, $user, $file->name);

            return $this->asJson([
                'success' => true,
                'photo' => $user->getPhoto()->getUrl($transform),
            ]);

        } catch (\Throwable $exception) {

            if (isset($fileLocation))
            {
                FileHelper::unlink($fileLocation);
            }
            Craft::error('There was an error uploading the photo: '.$exception->getMessage(), __METHOD__);
            return $this->asErrorJson(Craft::t('app', 'There was an error uploading your photo: {error}', [
                'error' => $exception->getMessage()
            ]));
        }
    }

    public function actionDeleteUserPhoto()
    {
        $this->requireAcceptsJson();
        $this->requireLogin();

        $user = Craft::$app->getUser()->getIdentity();
        if ($user->photoId) {
            Craft::$app->getElements()->deleteElementById($user->photoId, Asset::class);
        }
        $user->photoId = null;
        Craft::$app->getElements()->saveElement($user, false);
        return $this->asJson(['success' => true]);
    }

}
