<?php

namespace craftnet\controllers\api\v1;

use craftnet\controllers\api\BaseApiController;
use craftnet\plugins\Plugin;
use yii\web\Response;

/**
 * Class PluginController
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
        $plugin = Plugin::find()
            ->id($pluginId)
            ->anyStatus()
            ->one();

        if (!$plugin) {
            return $this->asErrorJson("Couldn't find plugin");
        }

        return $this->asJson($this->transformPlugin($plugin, true));
    }
}
