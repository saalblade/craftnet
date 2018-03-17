<?php

namespace craftcom\controllers\id;

use Craft;
use craft\errors\UploadFailedException;
use craft\web\Controller;
use craft\web\UploadedFile;
use craftcom\errors\LicenseNotFoundException;
use craftcom\Module;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use Exception;
use Throwable;

/**
 * Class CmsLicensesController
 *
 * @package craftcom\controllers\id
 *
 * @property Module $module
 */
class CmsLicensesController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Claims a license.
     *
     * @return Response
     * @throws LicenseNotFoundException
     */
    public function actionClaim(): Response
    {
        $key = Craft::$app->getRequest()->getParam('key');
        $licenseFile = UploadedFile::getInstanceByName('licenseFile');

        try {
            $user = Craft::$app->getUser()->getIdentity();

            if ($licenseFile) {
                if ($licenseFile->getHasError()) {
                    throw new UploadFailedException($licenseFile->error);
                }

                $licenseFilePath = $licenseFile->tempName;

                $key = file_get_contents($licenseFilePath);
            }

            if ($key) {
                $license = $this->module->getCmsLicenseManager()->getLicenseByKey($key);

                if ($license && $user) {
                    if (!$license->ownerId) {
                        $license->ownerId = $user->id;

                        if ($this->module->getCmsLicenseManager()->saveLicense($license)) {
                            return $this->asJson(['success' => true]);
                        }

                        throw new Exception("Couldn't save license.");
                    }

                    throw new Exception("License has already been claimed.");
                }

                throw new LicenseNotFoundException($key);
            }

            throw new Exception("No license key provided.");
        } catch (Throwable $e) {
            return $this->asErrorJson($e->getMessage());
        }
    }

    /**
     * Download license file.
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws \yii\web\HttpException
     */
    public function actionDownload(): Response
    {
        $user = Craft::$app->getUser()->getIdentity();
        $licenseId = Craft::$app->getRequest()->getParam('id');
        $license = $this->module->getCmsLicenseManager()->getLicenseById($licenseId);

        if ($license->ownerId === $user->id) {
            return Craft::$app->getResponse()->sendContentAsFile($license->key, 'license.key');
        }

        throw new ForbiddenHttpException('User is not authorized to perform this action');
    }

    /**
     * Returns licenses for the current user.
     *
     * @return Response
     * @throws LicenseNotFoundException
     */
    public function actionGetLicenses(): Response
    {
        $user = Craft::$app->getUser()->getIdentity();

        try {
            $licenses = Module::getInstance()->getCmsLicenseManager()->getLicensesArrayByOwner($user);

            return $this->asJson($licenses);
        } catch (Throwable $e) {
            return $this->asErrorJson($e->getMessage());
        }
    }

    /**
     * Releases a license.
     *
     * @return Response
     * @throws LicenseNotFoundException
     */
    public function actionRelease(): Response
    {
        $key = Craft::$app->getRequest()->getParam('key');
        $user = Craft::$app->getUser()->getIdentity();
        $license = $this->module->getCmsLicenseManager()->getLicenseByKey($key);

        try {
            if ($license && $user && $license->ownerId === $user->id) {
                $license->ownerId = null;

                if ($this->module->getCmsLicenseManager()->saveLicense($license)) {
                    return $this->asJson(['success' => true]);
                }

                throw new Exception("Couldn't save license.");
            }

            throw new LicenseNotFoundException($key);
        } catch (Throwable $e) {
            return $this->asErrorJson($e->getMessage());
        }
    }

    /**
     * Saves a license.
     *
     * @return Response
     * @throws LicenseNotFoundException
     */
    public function actionSave(): Response
    {
        $key = Craft::$app->getRequest()->getParam('key');
        $user = Craft::$app->getUser()->getIdentity();
        $license = $this->module->getCmsLicenseManager()->getLicenseByKey($key);

        try {
            if ($license && $user && $license->ownerId === $user->id) {
                $license->domain = Craft::$app->getRequest()->getParam('domain');
                $license->notes = Craft::$app->getRequest()->getParam('notes');

                if ($this->module->getCmsLicenseManager()->saveLicense($license)) {
                    return $this->asJson(['success' => true]);
                }

                throw new Exception("Couldn't save license.");
            }

            throw new LicenseNotFoundException($key);
        } catch (Throwable $e) {
            return $this->asErrorJson($e->getMessage());
        }
    }
}
