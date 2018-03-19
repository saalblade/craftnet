<?php

namespace craftnet\controllers\id;

use Craft;
use craft\web\Controller;
use craftnet\errors\LicenseNotFoundException;
use craftnet\Module;
use Exception;
use Throwable;
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
}
