<?php

namespace craftcom\controllers\id;

use Craft;
use craft\elements\Asset;
use craft\errors\UploadFailedException;
use craft\helpers\Assets;
use craft\helpers\Db;
use craft\helpers\FileHelper;
use craft\web\Controller;
use craft\web\UploadedFile;
use craftcom\records\VcsToken;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Class AccountController
 *
 * @package craftcom\controllers\id
 */
class AccountController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Account index.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        $stripeAccessToken = null;
        $user = Craft::$app->getUser()->getIdentity();

        $craftIdConfig = Craft::$app->getConfig()->getConfigFromFile('craftid');
        $stripePublicKey = $craftIdConfig['stripePublicKey'];

        return $this->renderTemplate('account/index', [
            'stripeAccessToken' => $user->stripeAccessToken,
            'stripePublicKey' => $stripePublicKey
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

        if ($userId != Craft::$app->getUser()->getIdentity()->id) {
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

    /**
     * Delete all the photos for current user.
     *
     * @return Response
     */
    public function actionDeleteUserPhoto(): Response
    {
        $this->requireAcceptsJson();
        $this->requireLogin();
        $userId = Craft::$app->getRequest()->getRequiredBodyParam('userId');

        if ($userId != Craft::$app->getUser()->getIdentity()->id) {
            $this->requirePermission('editUsers');
        }

        $user = Craft::$app->getUsers()->getUserById($userId);

        if ($user->photoId) {
            Craft::$app->getElements()->deleteElementById($user->photoId, Asset::class);
        }

        $user->photoId = null;
        Craft::$app->getElements()->saveElement($user, false);

        return $this->asJson([
            'photoId' => $user->photoId,
            'photoUrl' => $user->getThumbUrl(200),
        ]);
    }
}
