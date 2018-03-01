<?php

namespace craftcom\controllers\api\v1;

use craftcom\cms\CmsLicense;
use craftcom\cms\CmsLicenseManager;
use craftcom\controllers\api\BaseApiController;
use craftcom\helpers\LicenseHelper;
use yii\base\Exception;
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

        // todo: set domain to just the domain, not the full hostname
        $license = new CmsLicense([
            'expirable' => true,
            'expired' => false,
            'edition' => CmsLicenseManager::EDITION_PERSONAL,
            'email' => $payload->email,
            'domain' => $payload->hostname,
            'key' => LicenseHelper::generateKey(250, '!#$%^&*=+/'),
        ]);

        if (!$this->module->getCmsLicenseManager()->saveLicense($license)) {
            throw new Exception('Could not create CMS license: '.implode(',', $license->getFirstErrors()));
        }

        return $this->asJson([
            'license' => $license->toArray()
        ]);
    }
}
