<?php

namespace craftnet\controllers\id;

use Craft;
use craft\web\Controller;
use craftnet\errors\LicenseNotFoundException;
use craftnet\Module;
use Exception;
use Throwable;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * Class PluginLicensesController
 *
 * @property Module $module
 */
class PluginLicensesController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Claims a license.
     *
     * @return Response
     */
    public function actionClaim(): Response
    {
        $key = Craft::$app->getRequest()->getParam('key');
        $user = Craft::$app->getUser()->getIdentity();

        try {
            $this->module->getPluginLicenseManager()->claimLicense($user, $key);
            return $this->asJson(['success' => true]);
        } catch (Throwable $e) {
            return $this->asErrorJson($e->getMessage());
        }
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
            $licenses = Module::getInstance()->getPluginLicenseManager()->getLicensesArrayByOwner($user);
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
        $pluginHandle = Craft::$app->getRequest()->getParam('handle');
        $key = Craft::$app->getRequest()->getParam('key');
        $user = Craft::$app->getUser()->getIdentity();
        $manager = $this->module->getPluginLicenseManager();
        $license = $manager->getLicenseByKey($pluginHandle, $key);

        try {
            if ($license && $user && $license->ownerId === $user->id) {
                $license->ownerId = null;

                if ($manager->saveLicense($license)) {
                    $manager->addHistory($license->id, "released by {$user->email}");
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
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave(): Response
    {
        $pluginHandle = Craft::$app->getRequest()->getRequiredParam('pluginHandle');
        $key = Craft::$app->getRequest()->getRequiredParam('key');
        $user = Craft::$app->getUser()->getIdentity();
        $manager = $this->module->getPluginLicenseManager();
        $license = $manager->getLicenseByKey($pluginHandle, $key);

        try {
            if ($user && $license->ownerId === $user->id) {
                $license->notes = Craft::$app->getRequest()->getParam('notes');

                if ($manager->saveLicense($license)) {
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
     * Unlink a plugin license from a CMS license.
     *
     * @return Response
     * @throws LicenseNotFoundException
     */
    public function actionUnlink(): Response
    {
        $this->requireLogin();

        $pluginHandle = Craft::$app->getRequest()->getParam('handle');
        $key = Craft::$app->getRequest()->getParam('key');
        $user = Craft::$app->getUser()->getIdentity();
        $manager = $this->module->getPluginLicenseManager();
        $license = $manager->getLicenseByKey($pluginHandle, $key);

        try {
            if (!$license) {
                throw new LicenseNotFoundException($key);
            }

            if ($license->ownerId !== $user->id || !$license->cmsLicenseId) {
                throw new ForbiddenHttpException('User is not authorized to perform this action');
            }

            $cmsLicense = $this->module->getCmsLicenseManager()->getLicenseById($license->cmsLicenseId);

            if ($cmsLicense->ownerId !== $user->id) {
                throw new ForbiddenHttpException('User is not authorized to perform this action');
            }

            $license->cmsLicenseId = null;

            if ($manager->saveLicense($license)) {
                $manager->addHistory($license->id, "unlinked by {$user->email}");

                return $this->asJson(['success' => true]);
            }

            throw new Exception("Couldn't save license.");
        } catch (Throwable $e) {
            return $this->asErrorJson($e->getMessage());
        }
    }
}
