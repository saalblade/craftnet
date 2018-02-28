<?php

namespace craftcom\controllers\api\v1\licenses;

use Craft;
use craftcom\cms\CmsEdition;
use craftcom\cms\CmsLicense;
use craftcom\cms\CmsLicenseManager;
use craftcom\controllers\api\BaseApiController;
use craftcom\helpers\LicenseHelper;
use yii\base\Exception;
use yii\web\Response;

/**
 * Class CmsController
 */
class CmsController extends BaseApiController
{
    // Public Methods
    // =========================================================================

    /**
     * Handles /v1/account requests.
     *
     * @return Response
     */
    public function actionRequest(): Response
    {
        $payload = $this->getPayload('request-cms-license-request');

        $license = new CmsLicense([
            'expirable' => true,
            'expired' => false,
            'edition' => CmsLicenseManager::EDITION_PERSONAL,
            'email' => $payload->email,
            'hostname' => $payload->hostname,
            'key' => LicenseHelper::generateKey(250, '!#$%^&*=+/'),
        ]);

        if (!$this->module->getCmsLicenseManager()->saveLicense($license)) {
            throw new Exception('Could not create CMS license: '.implode(',', $license->getFirstErrors()));
        }

        return $this->asJson([
            'key' => $license->key,
        ]);
    }
}
