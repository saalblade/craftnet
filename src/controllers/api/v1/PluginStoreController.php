<?php

namespace craftnet\controllers\api\v1;

use Craft;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
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
        $cacheKey = 'pluginStoreData--' . $this->cmsVersion;

        if ($enablePluginStoreCache) {
            $pluginStoreData = Craft::$app->getCache()->get($cacheKey);
        }

        if (!$pluginStoreData) {
            $plugins = Plugin::find()
                ->withLatestReleaseInfo(true, $this->cmsVersion)
                ->with(['developer', 'categories', 'icon'])
                ->indexBy('id')
                ->all();

            $pluginStoreData = [
                'categories' => $this->_categories(),
                'featuredPlugins' => $this->_featuredPlugins(),
                'plugins' => $this->_plugins($plugins),
                'expiryDateOptions' => $this->_expiryDateOptions(),
            ];

            if ($enablePluginStoreCache) {
                Craft::$app->getCache()->set($cacheKey, $pluginStoreData, null, new FileDependency([
                    'fileName' => $this->module->getJsonDumper()->composerWebroot . '/packages.json',
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
                'iconUrl' => $icon ? $icon->getUrl() . '?' . $icon->dateModified->getTimestamp() : null,
            ];
        }

        return $ret;
    }

    /**
     * @return array
     * @throws \yii\base\Exception
     */
    private function _featuredPlugins(): array
    {
        $ret = [];

        $recents = Plugin::find()
            ->orderBy(['craftnet_plugins.dateApproved' => SORT_DESC])
            ->limit(10)
            ->withLatestReleaseInfo(true, $this->cmsVersion)
            ->andWhere(['not', ['craftnet_plugins.dateApproved' => null]])
            ->ids();

        $recentlyAddedPluginsEntry = Entry::find()->site('plugins')->section('recentlyAddedPlugins')->one();

        $ret[] = [
            'id' => 'recently-added',
            'slug' => 'recently-added',
            'title' => $recentlyAddedPluginsEntry->title,
            'description' => $recentlyAddedPluginsEntry->description,
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
            $pluginIds = [];

            foreach ($entry->plugins as $plugin) {
                $pluginIds[] = $plugin->id;
            }

            if (!empty($pluginIds)) {
                $ret[] = [
                    'id' => $entry->id,
                    'slug' => $entry->slug,
                    'title' => $entry->title,
                    'plugins' => $pluginIds,
                    'limit' => $entry->limit,
                ];
            }
        }

        return $ret;
    }

    /**
     * @param Plugin[] $plugins
     * @return array
     * @throws \craftnet\errors\MissingTokenException
     * @throws \yii\base\InvalidConfigException
     */
    private function _plugins(array $plugins): array
    {
        $ret = [];

        foreach ($plugins as $plugin) {
            $ret[] = $this->transformPlugin($plugin, false);
        }

        return $ret;
    }

    /**
     * @return array`
     */
    private function _expiryDateOptions(): array
    {
        $dates = [];

        for ($i = 1; $i <= 5; $i++) {
            $date = (new \DateTime('now', new \DateTimeZone('UTC')))
                ->modify("+{$i} years");
            $dates[] = ["{$i}y", $date->format('Y-m-d')];
        }

        return $dates;
    }
}
