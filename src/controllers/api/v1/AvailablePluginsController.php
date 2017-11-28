<?php

namespace craftcom\controllers\api\v1;

use Craft;
use craftcom\controllers\api\BaseApiController;
use craftcom\plugins\Plugin;
use yii\helpers\Inflector;
use yii\web\Response;

/**
 * Class PluginController
 *
 * @package craftcom\controllers\api\v1
 */
class AvailablePluginsController extends BaseApiController
{
    // Public Methods
    // =========================================================================

    /**
     * Handles /v1/available-plugins requests.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        // todo: remove this when RC1 launches
        return $this->asJson([]);

        $this->requirePostRequest();
        $clientInfo = $this->getPayload();
        $craft2Plugins = require(Craft::getAlias('@config/craft2-plugins.php'));

        $pluginHandles = [];

        // Get the new plugin handles for the plugins they already have installed
        $newHandlesByOld = [];

        if (isset($clientInfo->plugins)) {
            foreach ($clientInfo->plugins as $oldHandle) {
                if (isset($craft2Plugins[$oldHandle]['handle'])) {
                    $newHandle = $craft2Plugins[$oldHandle]['handle'];
                } else {
                    $newHandle = Inflector::camel2id($oldHandle);
                }
                $pluginHandles[] = $newHandle;
                $newHandlesByOld[$oldHandle] = $newHandle;
            }
        }
        $oldHandlesByNew = array_flip($newHandlesByOld);

        // Get the volume type plugin handles
        if (isset($clientInfo->assetSourceTypes)) {
            if (in_array('GoogleCloud', $clientInfo->assetSourceTypes)) {
                $pluginHandles[] = 'google-cloud';
            }
            if (in_array('Rackspace', $clientInfo->assetSourceTypes)) {
                $pluginHandles[] = 'rackspace';
            }
            if (in_array('S3', $clientInfo->assetSourceTypes)) {
                $pluginHandles[] = 'aws-s3';
            }
        }

        $res = [];

        $plugins = Plugin::find()
            ->handle($pluginHandles)
            ->with(['icon', 'developer'])
            ->all();

        foreach ($plugins as $plugin) {
            $key = $oldHandlesByNew[$plugin->handle] ?? $plugin->handle;
            $icon = $plugin->getIcon();
            $developer = $plugin->getDeveloper();
            $res[$key] = [
                'statusColor' => 'green',
                'status' => "[Available]({$plugin->repository})",
                'iconUrl' => $icon ? $icon->getUrl().'?'.$icon->dateModified->getTimestamp() : null,
                'name' => $plugin->name,
                'price' => $plugin->price,
                'currency' => 'USD',
                'developerName' => $developer->getDeveloperName(),
                'developerUrl' => $developer->developerUrl,
            ];
        }

        // Anything we missed?
        if (isset($clientInfo->plugins)) {
            foreach ($clientInfo->plugins as $oldHandle) {
                if (!isset($res[$oldHandle])) {
                    $res[$oldHandle] = [
                        'statusColor' => '',
                        'status' => 'Not available yet',
                    ];
                }

                if (isset($craft2Plugins[$oldHandle])) {
                    $res[$oldHandle] = array_merge($res[$oldHandle], $craft2Plugins[$oldHandle]);
                }
            }
        }

        return $this->asJson($res);
    }
}
