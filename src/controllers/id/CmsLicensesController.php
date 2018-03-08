<?php

namespace craftcom\controllers\id;

use Craft;
use craft\web\Controller;
use craftcom\errors\LicenseNotFoundException;
use craftcom\Module;
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
            if($license && $user && $license->ownerId === $user->id) {
                $license->domain = Craft::$app->getRequest()->getParam('domain');
                $license->notes = Craft::$app->getRequest()->getParam('notes');

                if ($this->module->getCmsLicenseManager()->saveLicense($license)) {
                    return $this->asJson(['success' => true]);
                }

                throw new Exception("Couldn't save license.");
            }

            throw new LicenseNotFoundException($key);
        } catch(Throwable $e) {
            return $this->asErrorJson($e->getMessage());
        }
    }
}
