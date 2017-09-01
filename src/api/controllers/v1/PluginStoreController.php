<?php

namespace craftcom\api\controllers\v1;

use Craft;
use craft\elements\Entry;
use craftcom\api\controllers\BaseApiController;
use yii\web\Response;

/**
 * Class PluginStoreController
 *
 * @package craftcom\api\controllers\v1
 */
class PluginStoreController extends BaseApiController
{
    // Public Methods
    // =========================================================================

    /**
     * Handles /v1/craft-id requests.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        header("Access-Control-Allow-Origin: *");

        $pluginStoreData = Craft::$app->getCache()->get('pluginStoreData');

        if(!$pluginStoreData) {
            // Featured Plugins

            $featuredPluginEntries = Entry::find()->section('featuredPlugins')->all();

            $featuredPlugins = [];

            foreach($featuredPluginEntries as $featuredPluginEntry) {
                $plugins = [];

                foreach($featuredPluginEntry->plugins as $plugin) {
                    $plugins[] = $plugin->id;
                }

                $featuredPlugins[] = [
                    'id' => $featuredPluginEntry->id,
                    'title' => $featuredPluginEntry->title,
                    'plugins' => $plugins,
                    'limit' => $featuredPluginEntry->limit,
                ];
            }


            // Categories

            $_categories = \craft\elements\Category::find()->all();
            $categories = [];

            foreach($_categories as $category) {
                $iconUrl = null;
                $icon = $category->icon->one();

                if($icon) {
                    $iconUrl = $icon->getUrl();
                }

                $categories[] = [
                    'id' => $category->id,
                    'title' => $category->title,
                    'slug' => $category->slug,
                    'iconUrl' => $iconUrl,
                ];
            }


            // Plugins

            $plugins = [];

            $pluginEntries = Entry::find()->section('plugins')->all();

            foreach($pluginEntries as $pluginEntry) {
                $plugins[] = $this->pluginTransformer($pluginEntry);
            }

            $pluginStoreData = [
                'featuredPlugins' => $featuredPlugins,
                'categories' => $categories,
                'plugins' => $plugins,
            ];

            Craft::$app->getCache()->set('pluginStoreData', $pluginStoreData, ( 10 * 60 ));
        }

        return $this->asJson($pluginStoreData);
    }
}
