<?php

namespace craftcom\controllers\api\v1;

use Craft;
use craft\elements\Entry;
use craftcom\controllers\api\BaseApiController;
use craftcom\plugins\Plugin;
use yii\web\Response;

/**
 * Class PluginStoreController
 *
 * @package craftcom\controllers\api\v1
 */
class PluginStoreController extends BaseApiController
{
    // Properties
    // =========================================================================

    /**
     * @var int
     */
    private $craftClientPluginId = 273;

    /**
     * @var int
     */
    private $craftProPluginId = 275;

    // Public Methods
    // =========================================================================

    /**
     * Handles /v1/plugin-store requests.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        $enableCraftId = (Craft::$app->getRequest()->getParam('enableCraftId') === '1' ? true : false);

        $cacheKey = 'pluginStoreData';

        if ($enableCraftId) {
            $cacheKey = 'pluginStoreDataCraftId';
        }

        $pluginStoreData = null;

        $craftIdConfig = Craft::$app->getConfig()->getConfigFromFile('craftid');

        $enablePluginStoreCache = $craftIdConfig['enablePluginStoreCache'];

        if ($enablePluginStoreCache) {
            $pluginStoreData = Craft::$app->getCache()->get($cacheKey);
        }

        if (!$pluginStoreData) {
            // Featured Plugins

            $featuredPluginEntries = Entry::find()->section('featuredPlugins')->all();

            $featuredPlugins = [];

            foreach ($featuredPluginEntries as $featuredPluginEntry) {
                $plugins = [];

                $pluginElements = $featuredPluginEntry->plugins;

                foreach ($pluginElements->all() as $plugin) {
                    if ($plugin) {
                        if ($enableCraftId || (!$enableCraftId && !$plugin->price)) {
                            $plugins[] = $plugin->id;
                        }
                    }
                }

                $featuredPlugins[] = [
                    'id' => $featuredPluginEntry->id,
                    'title' => $featuredPluginEntry->title,
                    'plugins' => $plugins,
                    'limit' => $featuredPluginEntry->limit,
                ];
            }


            // Categories

            $_categories = \craft\elements\Category::find()->orderBy('title asc')->all();
            $categories = [];

            foreach ($_categories as $category) {
                $iconUrl = null;
                $icon = $category->icon->one();

                if ($icon) {
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

            $query = Plugin::find();

            if (!$enableCraftId) {
                $query->andWhere(['price' => null]);
            }

            foreach ($query->all() as $pluginElement) {
                $plugins[] = $this->transformPlugin($pluginElement, true);
            }

            $pluginStoreData = [
                'featuredPlugins' => $featuredPlugins,
                'categories' => $categories,
                'plugins' => $plugins,
                'craftClientPluginId' => $this->craftClientPluginId,
                'craftProPluginId' => $this->craftProPluginId,
            ];

            if ($enablePluginStoreCache) {
                Craft::$app->getCache()->set($cacheKey, $pluginStoreData, (60 * 60 * 3));
            }
        }

        return $this->asJson($pluginStoreData);
    }
}
