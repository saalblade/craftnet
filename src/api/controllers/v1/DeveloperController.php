<?php

namespace craftcom\api\controllers\v1;

use Craft;
use craft\elements\User;
use craftcom\api\controllers\BaseApiController;
use craftcom\plugins\Plugin;
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
        $enableCraftId = (Craft::$app->getRequest()->getParam('enableCraftId') === '1' ? true : false);

        $user = User::find()->id($userId)->status(null)->one();

        if ($user) {
            $plugins = [];
            $query = Plugin::find()->developerId($user->id)->orderBy('name asc');

            if (!$enableCraftId) {
                $query->price('00.00');
            }

            foreach ($query->all() as $element) {
                $plugins[] = $this->pluginTransformer($element);
            }

            return $this->asJson([
                'developerName' => $user->developerName,
                'developerUrl' => $user->developerUrl,
                'location' => $user->location,
                'username' => $user->username,
                'fullName' => $user->getFullName(),
                'email' => $user->email,
                'plugins' => $plugins,
                // 'photoUrl' => ($user->getPhoto() ? $user->getPhoto()->getThumb(200) : null),
                'photoUrl' => ($user->getPhoto() ? $user->getPhoto()->getUrl() : null),
            ]);
        }

        return $this->asErrorJson("Couldn’t find developer");
    }
}
