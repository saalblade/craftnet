<?php

namespace craftnet\controllers;

use Craft;
use craft\base\Element;
use craft\web\Controller;
use craftnet\Module;
use craftnet\partners\Partner;
use craftnet\partners\PartnerAsset;
use craftnet\partners\PartnerCapabilitiesQuery;
use craftnet\partners\PartnerService;
use yii\base\Exception;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @property Module $module
 */
class PartnersController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        return true;
    }

    /**
     * @param int|null $partnerId
     * @param Partner|null $partner
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionEdit(int $partnerId = null, Partner $partner = null): Response
    {
        if ($partner === null) {
            if ($partnerId !== null) {
                $partner = Partner::find()->id($partnerId)->status(null)->one();

                if ($partner === null) {
                    throw new NotFoundHttpException('Invalid partner ID: '.$partnerId);
                }
            } else {
                $partner = new Partner([
                    'enabled' => false,
                ]);
            }
        }

        $allCapabilities = (new PartnerCapabilitiesQuery())->asIndexedTitles()->all();
        $title = $partner->id ? $partner->businessName : 'Add a new partner';
        $folderIds = PartnerService::getInstance()->getVolumeFolderIds();

        $this->view->registerAssetBundle(PartnerAsset::class);

        return $this->renderTemplate('craftnet/partners/_edit', compact(
            'partner',
            'title',
            'allCapabilities',
            'folderIds'
        ));
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    /**
     * TODO: Implement user permissions for editing partners
     * @return Response
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionSave()
    {
        $request = Craft::$app->getRequest();
        $partnerId = $request->getBodyParam('partnerId');

        // Get existing or new Partner
        if ($partnerId) {
            $partner = Partner::find()->id($partnerId)->status(null)->one();
        } else {
            $partner = new Partner();
        }

        // Ensure Partner
        if ($partner === null) {
            throw new NotFoundHttpException('Invalid partner ID: ' . $partnerId);
        }

        $partner->enabled = $request->getBodyParam('enabled');
        $partner->ownerId = ((array) $request->getBodyParam('ownerId'))[0];
        $partner->businessName = $request->getBodyParam('businessName');
        $partner->primaryContactName = $request->getBodyParam('primaryContactName');
        $partner->primaryContactEmail = $request->getBodyParam('primaryContactEmail');
        $partner->primaryContactPhone = $request->getBodyParam('primaryContactPhone');
        $partner->businessSummary = $request->getBodyParam('businessSummary');
        $partner->minimumBudget = $request->getBodyParam('minimumBudget');
        $partner->setMsaAssetIdFromPost($request->getBodyParam('msa'));
        $partner->setCapabilities($request->getBodyParam('capabilities', []));
        $partner->setLocationsFromPost($request->getBodyParam('locations', []));
        $partner->setProjectsFromPost($request->getBodyParam('projects', []));

        if ($partner->enabled) {
            $partner->setScenario(Element::SCENARIO_LIVE);
        }

        if (!Craft::$app->getElements()->saveElement($partner)) {
            if ($request->getAcceptsJson()) {
                return $this->asJson([
                    'errors' => $partner->getErrors(),
                ]);
            }

            Craft::$app->getSession()->setError('Couldn’t save partner.');
            Craft::$app->getUrlManager()->setRouteParams([
                'partner' => $partner
            ]);
            return null;
        }

        return $this->redirectToPostedUrl($partner);
    }

    /**
     * @return null|Response
     * @throws NotFoundHttpException
     */
    public function actionDelete()
    {
        $request = Craft::$app->getRequest();
        $partnerId = $request->getBodyParam('partnerId');
        $partner = Partner::find()->id($partnerId)->status(null)->one();

        if (!$partner) {
            throw new NotFoundHttpException('Plugin not found');
        }

        return $this->redirectToPostedUrl($partner);
    }

    public function actionFoo()
    {
        return '';
    }

    // Private Methods
    // =========================================================================

}
