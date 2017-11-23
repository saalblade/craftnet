<?php

namespace craftcom\controllers\api\v1;

use Craft;
use craftcom\controllers\api\BaseApiController;
use craftcom\plugins\Plugin;
use yii\web\Response;

/**
 * Class PluginController
 *
 * @package craftcom\controllers\api\v1
 */
class PluginController extends BaseApiController
{
    // Public Methods
    // =========================================================================

    /**
     * Handles /v1/plugin/<pluginId> requests.
     *
     * @return Response
     */
    public function actionIndex($pluginId): Response
    {
        $plugin = Plugin::find()->id($pluginId)->status(null)->one();

        if (!$plugin) {
            return $this->asErrorJson("Couldn't find plugin");
        }

        $enableCraftId = (bool)Craft::$app->getRequest()->getParam('enableCraftId', false);

        return $this->asJson($this->transformPlugin($plugin, true, $enableCraftId));
    }
}
