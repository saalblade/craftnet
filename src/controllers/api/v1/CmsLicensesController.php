<?php

namespace craftcom\controllers\api\v1;

use craftcom\cms\CmsLicense;
use craftcom\cms\CmsLicenseManager;
use craftcom\controllers\api\BaseApiController;
use craftcom\errors\LicenseNotFoundException;
use craftcom\helpers\LicenseHelper;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Class CmsLicensesController
 */
class CmsLicensesController extends BaseApiController
{
    // Properties
    // =========================================================================

    public $defaultAction = 'create';

    // Public Methods
    // =========================================================================

    /**
     * Creates a new CMS license.
     *
     * @return Response
     */
    public function actionCreate(): Response
    {
        $payload = $this->getPayload('create-cms-license-request');

        $license = new CmsLicense([
            'expirable' => true,
            'expired' => false,
            'edition' => CmsLicenseManager::EDITION_PERSONAL,
            'email' => $payload->email,
            'domain' => $payload->hostname,
            'key' => LicenseHelper::generateKey(250, '!#$%^&*=+/'),
        ]);

        if (!$this->module->getCmsLicenseManager()->saveLicense($license)) {
            throw new Exception('Could not create CMS license: '.implode(', ', $license->getErrorSummary(true)));
        }

        return $this->asJson([
            'license' => $license->toArray()
        ]);
    }

    /**
     * Retrieves a CMS license.
     *
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionGet(string $key): Response
    {
        try {
            $license = $this->module->getCmsLicenseManager()->getLicenseByKey($key);
        } catch (LicenseNotFoundException $e) {
            throw new BadRequestHttpException($e->getMessage(), 0, $e);
        }

        return $this->asJson([
            'license' => $license,
        ]);
    }
}
