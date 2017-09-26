<?php

namespace craftcom\id\controllers;

use Craft;
use craft\helpers\Db;
use craft\records\OAuthToken;
use craft\web\Controller;

/**
 * Class AccountController
 *
 * @package craftcom\id\controllers
 */
class AccountController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionIndex()
    {
        $stripeAccessToken = null;
        $userId = Craft::$app->getUser()->id;

        if ($userId) {
            $stripeAccessToken = OAuthToken::find()
                ->where(Db::parseParam('userId', $userId))
                ->andWhere(Db::parseParam('provider', 'Stripe'))
                ->one();
        }

        return $this->renderTemplate('account/index', [
            'stripeAccessToken' => $stripeAccessToken
        ]);
    }
}
