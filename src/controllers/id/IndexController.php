<?php

namespace craftnet\controllers\id;

use Craft;
use craft\web\Controller;
use yii\web\Response;

/**
 * Class IndexController
 */
class IndexController extends Controller
{
    // Properties
    // =========================================================================

    protected $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    /**
     * Account index.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        $stripeAccessToken = null;
        $user = Craft::$app->getUser()->getIdentity();

        if ($user) {
            $stripeAccessToken = $user->stripeAccessToken;
        }

        $craftIdConfig = Craft::$app->getConfig()->getConfigFromFile('craftid');
        $stripePublicKey = $craftIdConfig['stripePublicKey'];

        return $this->renderTemplate('index', [
            'stripeAccessToken' => $stripeAccessToken,
            'stripePublicKey' => $stripePublicKey
        ]);
    }
}
