<?php

namespace craftcom\id\controllers;

use Craft;
use craft\helpers\Db;
use craft\records\OAuthToken;
use craft\web\Controller;

/**
 * Class AccountController
 *
 * @package craftcom\id\controllers
 */
class AccountController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionIndex()
    {
        $stripeAccessToken = null;
        $userId = Craft::$app->getUser()->id;

        if ($userId) {
            $stripeAccessToken = OAuthToken::find()
                ->where(Db::parseParam('userId', $userId))
                ->andWhere(Db::parseParam('provider', 'Stripe'))
                ->one();
        }

        return $this->renderTemplate('account/index', [
            'stripeAccessToken' => $stripeAccessToken
        ]);
    }

    /**
     * Upload a user photo.
     *
     * @return Response|null
     * @throws BadRequestHttpException if the uploaded file is not an image
     */
    public function actionUploadUserPhoto()
    {
        $this->requireAcceptsJson();
        $this->requireLogin();
        $userId = Craft::$app->getRequest()->getRequiredBodyParam('userId');

        if ($userId !== Craft::$app->getUser()->getIdentity()->id) {
            $this->requirePermission('editUsers');
        }

        if (($file = UploadedFile::getInstanceByName('photo')) === null) {
            return null;
        }

        try {
            if ($file->getHasError()) {
                throw new UploadFailedException($file->error);
            }

            $users = Craft::$app->getUsers();
            $user = $users->getUserById($userId);

            // Move to our own temp location
            $fileLocation = Assets::tempFilePath($file->getExtension());
            move_uploaded_file($file->tempName, $fileLocation);
            $users->saveUserPhoto($fileLocation, $user, $file->name);

            return $this->asJson([
                'photoId' => $user->photoId,
                'photoUrl' => $user->getThumbUrl(200),
            ]);
        } catch (\Throwable $exception) {
            /** @noinspection UnSafeIsSetOverArrayInspection - FP */
            if (isset($fileLocation)) {
                FileHelper::removeFile($fileLocation);
            }

            Craft::error('There was an error uploading the photo: '.$exception->getMessage(), __METHOD__);

            return $this->asErrorJson(Craft::t('app', 'There was an error uploading your photo: {error}', [
                'error' => $exception->getMessage()
            ]));
        }
    }

}
