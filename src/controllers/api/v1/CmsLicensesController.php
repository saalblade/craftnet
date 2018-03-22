<?php

namespace craftnet\controllers\api\v1;

use Craft;
use craftnet\cms\CmsLicense;
use craftnet\cms\CmsLicenseManager;
use craftnet\controllers\api\BaseApiController;
use craftnet\helpers\KeyHelper;
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
        $license = $this->createCmsLicense();

        $responseHeaders = Craft::$app->getResponse()->getHeaders()
            ->set('X-Craft-License-Status', self::LICENSE_STATUS_VALID)
            ->set('X-Craft-License-Domain', $license->domain)
            ->set('X-Craft-License-Edition', $license->edition);

        // was a host provided with the request?
        if (Craft::$app->getRequest()->getHeaders()->has('X-Craft-Host')) {
            $responseHeaders->set('X-Craft-Allow-Trials', (string)($license->domain === null));
        }

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
