<?php

namespace craftcom\controllers\api\v1;

use Craft;
use craft\elements\User;
use craftcom\controllers\api\BaseApiController;
use yii\web\Response;

/**
 * Class DeveloperController
 *
 * @package craftcom\controllers\api\v1
 */
class DeveloperController extends BaseApiController
{
    // Public Methods
    // =========================================================================

    /**
     * Handles /v1/developer/<userId> requests.
     *
     * @return Response
     */
    public function actionIndex($userId): Response
    {
        $user = User::find()->id($userId)->status(null)->one();

        if ($user) {
            return $this->asJson([
                'developerName' => strip_tags($user->developerName),
                'developerUrl' => $user->developerUrl,
                'location' => $user->location,
                'username' => $user->username,
                'fullName' => strip_tags($user->getFullName()),
                'email' => $user->email,
                'photoUrl' => ($user->getPhoto() ? $user->getPhoto()->getUrl(['width' => 200, 'height' => 200, 'mode' => 'fit']) : null),
            ]);
        }

        return $this->asErrorJson("Couldn’t find developer");
    }
}
