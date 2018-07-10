<?php

namespace craftnet\controllers\api\v1;

use Craft;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\helpers\ArrayHelper;
use craftnet\controllers\api\BaseApiController;
use craftnet\plugins\Plugin;
use yii\caching\FileDependency;
use yii\web\Response;

/**
 * Class PluginStoreController
 */
class PluginStoreController extends BaseApiController
{
    // Public Methods
    // =========================================================================

    /**
     * Handles /v1/plugin-store requests.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        $pluginStoreData = null;

        $craftIdConfig = Craft::$app->getConfig()->getConfigFromFile('craftid');
        $enablePluginStoreCache = $craftIdConfig['enablePluginStoreCache'];
        $cacheKey = 'pluginStoreData';

        if ($enablePluginStoreCache) {
            $pluginStoreData = Craft::$app->getCache()->get($cacheKey);
        }

        if (!$pluginStoreData) {
            $pluginStoreData = [
                'categories' => $this->_categories(),
                'featuredPlugins' => $this->_featuredPlugins(),
                'plugins' => $this->_plugins(),
            ];

            if ($enablePluginStoreCache) {
                Craft::$app->getCache()->set($cacheKey, $pluginStoreData, null, new FileDependency([
                    'fileName' => $this->module->getJsonDumper()->composerWebroot.'/packages.json',
                ]));
            }
        }

        return $this->asJson($pluginStoreData);
    }

    // Private Methods
    // =========================================================================

    private function _categories(): array
    {
        $ret = [];

        $categories = Category::find()
            ->group('pluginCategories')
            ->with('icon')
            ->all();

        foreach ($categories as $category) {
            /** @var Asset|null $icon */
            $icon = $category->icon[0] ?? null;
            $ret[] = [
                'id' => $category->id,
                'title' => $category->title,
                'description' => $category->description,
                'slug' => $category->slug,
                'iconUrl' => $icon ? $icon->getUrl().'?'.$icon->dateModified->getTimestamp() : null,
            ];
        }

        return $ret;
    }

    private function _featuredPlugins(): array
    {
        $ret = [];

        $recents = Plugin::find()
            ->orderBy(['craftnet_plugins.dateApproved' => SORT_DESC])
            ->limit(10)
            ->hasLatestVersion()
            ->andWhere(['not', ['craftnet_plugins.dateApproved' => null]])
            ->ids();

        $recentlyAddedPluginsSeo = Craft::$app->getGlobals()->getSetByHandle('recentlyAddedPluginsSeo');

        $ret[] = [
            'id' => 'recently-added',
            'slug' => 'recently-added',
            'title' => $recentlyAddedPluginsSeo->pageTitle,
            'description' => $recentlyAddedPluginsSeo->description,
            'plugins' => $recents,
            'limit' => 6,
        ];

        $entries = Entry::find()
            ->site('craftId')
            ->select(['elements.id', 'elements.fieldLayoutId', 'content.title', 'content.field_limit', 'content.field_description', 'elements_sites.slug'])
            ->section('featuredPlugins')
            ->with('plugins', ['select' => ['elements.id']])
            ->all();

        foreach ($entries as $entry) {
            $ret[] = [
                'id' => $entry->id,
                'slug' => $entry->slug,
                'title' => $entry->title,
                'description' => $entry->description,
                'plugins' => ArrayHelper::getColumn($entry->plugins, 'id'),
                'limit' => $entry->limit,
            ];
        }

        return $ret;
    }

    /**
     * @param bool $includePrices
     *
     * @return array
     */
    private function _plugins(): array
    {
        $ret = [];

        $plugins = Plugin::find()
            ->andWhere(['not', ['craftnet_plugins.latestVersion' => null]])
            ->with(['developer', 'categories', 'icon'])
            ->all();

        foreach ($plugins as $plugin) {
            $ret[] = $this->transformPlugin($plugin, false);
        }

        return $ret;
    }
}
