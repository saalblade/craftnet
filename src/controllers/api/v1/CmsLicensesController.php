<?php

namespace craftcom\controllers\api\v1;

use Craft;
use craftcom\cms\CmsLicense;
use craftcom\cms\CmsLicenseManager;
use craftcom\controllers\api\BaseApiController;
use craftcom\errors\LicenseNotFoundException;
use craftcom\helpers\LicenseHelper;
use yii\base\Exception;
use yii\validators\EmailValidator;
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
     * @throws BadRequestHttpException
     * @throws Exception
     */
    public function actionCreate(): Response
    {
        $headers = Craft::$app->getRequest()->getHeaders();
        if (($email = $headers->get('X-Craft-User-Email')) === null) {
            throw new BadRequestHttpException('Missing X-Craft-User-Email Header');
        }
        if ((new EmailValidator())->validate($email, $error) === false) {
            throw new BadRequestHttpException($error);
        }

        $license = new CmsLicense([
            'expirable' => true,
            'expired' => false,
            'edition' => CmsLicenseManager::EDITION_PERSONAL,
            'email' => $email,
            'domain' => $headers->get('X-Craft-Host'),
            'key' => LicenseHelper::generateCmsKey(),
            'lastEdition' => $this->cmsEdition,
            'lastVersion' => $this->cmsVersion,
            'lastActivityOn' => new \DateTime(),
        ]);

        if (!$this->module->getCmsLicenseManager()->saveLicense($license)) {
            throw new Exception('Could not create CMS license: '.implode(', ', $license->getErrorSummary(true)));
        }

        Craft::$app->getResponse()->getHeaders()
            ->set('X-Craft-License-Status', self::LICENSE_STATUS_VALID)
            ->set('X-Craft-License-Domain', $license->domain)
            ->set('X-Craft-License-Edition', $license->edition);

        // include this license in the request log
        $this->cmsLicenses[] = $license;

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
    public function actionGet(): Response
    {
        if (empty($this->cmsLicenses)) {
            throw new BadRequestHttpException('Missing X-Craft-License Header');
        }

        $license = reset($this->cmsLicenses);
        return $this->asJson([
            'license' => $license->toArray([], ['pluginLicenses']),
        ]);
    }
}
