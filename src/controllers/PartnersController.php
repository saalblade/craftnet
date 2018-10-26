<?php

namespace craftnet\controllers;

use Craft;
use craft\base\Element;
use craft\elements\User;
use craft\web\Controller;
use craftnet\developers\UserBehavior;
use craftnet\Module;
use craftnet\partners\Partner;
use craftnet\partners\PartnerAsset;
use craftnet\partners\PartnerCapabilitiesQuery;
use craftnet\partners\PartnerHistory;
use craftnet\partners\PartnerService;
use GuzzleHttp\Exception\RequestException;
use yii\base\Exception;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
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
     * Fetches the parter for the currently logged in user.
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionFetchPartner()
    {
        $this->requireLogin();
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        /** @var User|UserBehavior $user */
        $user = Craft::$app->getUser()->getIdentity();
        $partner = $user->getPartner();

        return $this->asJson($partner);
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
                $partner = Partner::find()->id($partnerId)->anyStatus()->one();

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
        $isNew = $partnerId === null;

        // Get existing or new Partner
        if ($partnerId) {
            $partner = Partner::find()->id($partnerId)->status(null)->one();

            if ($partner === null) {
                throw new NotFoundHttpException('Invalid partner ID: ' . $partnerId);
            }
        } else {
            $partner = new Partner();
        }

        $partner->enabled = $request->getBodyParam('enabled');
        $partner->ownerId = ((array) $request->getBodyParam('ownerId'))[0];
        $partner->businessName = $request->getBodyParam('businessName');
        $partner->primaryContactName = $request->getBodyParam('primaryContactName');
        $partner->primaryContactEmail = $request->getBodyParam('primaryContactEmail');
        $partner->primaryContactPhone = $request->getBodyParam('primaryContactPhone');
        $partner->fullBio = $request->getBodyParam('fullBio');
        $partner->shortBio = $request->getBodyParam('shortBio');
        $partner->hasFullTimeDev = $request->getBodyParam('hasFullTimeDev');
        $partner->isCraftVerified = $request->getBodyParam('isCraftVerified');
        $partner->isCommerceVerified = $request->getBodyParam('isCommerceVerified');
        $partner->isEnterpriseVerified = $request->getBodyParam('isEnterpriseVerified');
        $partner->isRegisteredBusiness = $request->getBodyParam('isRegisteredBusiness');
        $partner->agencySize = $request->getBodyParam('agencySize');
        $partner->hasFullTimeDev = $request->getBodyParam('hasFullTimeDev');
        $partner->region = $request->getBodyParam('region');
        $partner->expertise = $request->getBodyParam('expertise');
        $partner->setCapabilities($request->getBodyParam('capabilities', []));
        $partner->setLocationsFromPost($request->getBodyParam('locations', []));
        $partner->setProjectsFromPost($request->getBodyParam('projects', []));
        $partner->setVerificationStartDateFromPost($request->getBodyParam('verificationStartDate'));

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

        // "Save & continue" on a new entry tries to go to `partners/-`
        if ($isNew) {
            return $this->redirect('partners/' . $partner->id);
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
            throw new NotFoundHttpException('Partner not found');
        }

        Craft::$app->getElements()->deleteElement($partner);

        return $this->redirect('partners');
    }

    public function actionFetchHistory($partnerId)
    {
        $history = PartnerHistory::findByPartnerId($partnerId) ;
        return $this->asJson(compact('history', 'parnerId'));
    }

    public function actionSaveHistory()
    {
        $request = Craft::$app->getRequest();

        $params = [
            'id' => $request->getBodyParam('id'),
            'message' => $request->getBodyParam('message'),
            'partnerId' => $request->getBodyParam('partnerId'),
            'authorId' => Craft::$app->getUser()->id,
        ];

        $partnerHistory = PartnerHistory::firstOrNew($params);

        $success = $partnerHistory->save();

        if (!$success) {
            return $this->asJson([
                'success' => false,
                'payload' => $params,
                'errors' => $partnerHistory->getErrors(),
            ]);
        }

        return $this->asJson([
            'success' => true,
            'history' => $partnerHistory
        ]);
    }

    /**
     * @param $id
     * @return Response
     * @throws RequestException
     */
    public function actionDeleteHistory($id)
    {
        $rowsAffected = PartnerHistory::deleteById((int) $id);

        return $this->asJson([
            'success' => (bool) $rowsAffected
        ]);
    }

    public function actionFoo()
    {
        return '';
    }

    // Private Methods
    // =========================================================================

}
