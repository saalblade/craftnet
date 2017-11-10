<?php

namespace craftcom\api\controllers\v1;

use Craft;
use craft\elements\User;
use craftcom\api\controllers\BaseApiController;
use yii\web\Response;

/**
 * Class DeveloperController
 *
 * @package craftcom\api\controllers\v1
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
                'developerName' => $user->developerName,
                'developerUrl' => $user->developerUrl,
                'location' => $user->location,
                'username' => $user->username,
                'fullName' => $user->getFullName(),
                'email' => $user->email,
                // 'photoUrl' => ($user->getPhoto() ? $user->getPhoto()->getThumbUrl(200) : null),
                'photoUrl' => ($user->getPhoto() ? $user->getPhoto()->getUrl() : null),
            ]);
        }

        return $this->asErrorJson("Couldn’t find developer");
    }
}
