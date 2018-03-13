<?php

namespace craftcom\controllers\id;

use Craft;
use craft\web\Controller;
use craftcom\errors\LicenseNotFoundException;
use craftcom\Module;
use craftcom\plugins\Plugin;
use yii\web\Response;
use Exception;
use Throwable;

/**
 * Class PluginLicensesController
 *
 * @package craftcom\controllers\id
 *
 * @property Module $module
 */
class PluginLicensesController extends Controller
{
    // Public Methods
    // =========================================================================

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
        $license = $this->module->getPluginLicenseManager()->getLicenseByKey($pluginHandle, $key);

        try {
            if($license && $user && $license->ownerId === $user->id) {
                $license->ownerId = null;

                if ($this->module->getPluginLicenseManager()->saveLicense($license)) {
                    return $this->asJson(['success' => true]);
                }

                throw new Exception("Couldn't save license.");
            }

            throw new LicenseNotFoundException($key);
        } catch(Throwable $e) {
            return $this->asErrorJson($e->getMessage());
        }
    }

    /**
     * Claims a license.
     *
     * @return Response
     * @throws LicenseNotFoundException
     */
    public function actionClaim(): Response
    {
        $key = Craft::$app->getRequest()->getParam('key');
        $user = Craft::$app->getUser()->getIdentity();

        try {
            $this->module->getPluginLicenseManager()->claimLicense($user, $key);
            return $this->asJson(['success' => true]);
        } catch(Throwable $e) {
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
            $results = Module::getInstance()->getCmsLicenseManager()->getLicensesByOwner($user->id);

            $licenses = [];

            foreach($results as $result) {
                $license = $result->toArray();
                $pluginLicensesResults = Module::getInstance()->getPluginLicenseManager()->getLicensesByCmsLicenseId($result->id);

                $pluginLicenses = [];

                foreach ($pluginLicensesResults as $key => $pluginLicensesResult) {
                    $pluginLicense = $pluginLicensesResult->toArray();

                    $plugin = null;

                    if($pluginLicensesResult->pluginId) {
                        $plugin = Plugin::find()->id($pluginLicensesResult->pluginId)->status(null)->one();
                    }

                    $pluginLicense['plugin'] = $plugin;

                    $pluginLicenses[] = $pluginLicense;
                }



                $license['pluginLicenses'] = $pluginLicenses;
                $licenses[] = $license;
            }

            return $this->asJson($licenses);
        } catch(Throwable $e) {
            return $this->asErrorJson($e->getMessage());
        }
    }
}
