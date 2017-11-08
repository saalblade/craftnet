<?php

namespace craftcom\api\controllers\v1;

use Craft;
use craftcom\api\controllers\BaseApiController;
use craftcom\plugins\Plugin;
use yii\web\Response;

/**
 * Class PluginController
 *
 * @package craftcom\api\controllers\v1
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
        $pluginElement = Plugin::find()->id($pluginId)->status(null)->one();

        if ($pluginElement) {

            $plugin = $this->pluginTransformer($pluginElement);

            return $this->asJson($plugin);
        }

        return $this->asErrorJson("Couldn’t find plugin");
    }
}
