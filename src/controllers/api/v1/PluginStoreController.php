<?php

namespace craftcom\controllers\api\v1;

use Craft;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\helpers\ArrayHelper;
use craftcom\controllers\api\BaseApiController;
use craftcom\plugins\Plugin;
use yii\caching\FileDependency;
use yii\web\Response;

/**
 * Class PluginStoreController
 *
 * @package craftcom\controllers\api\v1
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

        if ($enablePluginStoreCache) {
            $pluginStoreData = Craft::$app->getCache()->get('pluginStoreData');
        }

        if (!$pluginStoreData) {
            $pluginStoreData = [
                'categories' => $this->_categories(),
                'featuredPlugins' => $this->_featuredPlugins(),
                'plugins' => $this->_plugins(),
            ];

            if ($enablePluginStoreCache) {
                Craft::$app->getCache()->set('pluginStoreData', $pluginStoreData, null, new FileDependency([
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
                'slug' => $category->slug,
                'iconUrl' => $icon ? $icon->getUrl().'?'.$icon->dateModified->getTimestamp() : null,
            ];
        }

        return $ret;
    }

    private function _featuredPlugins(): array
    {
        $ret = [];

        $entries = Entry::find()
            ->select(['elements.id', 'content.title', 'content.field_limit'])
            ->section('featuredPlugins')
            ->with('plugins', ['select' => ['elements.id']])
            ->all();

        foreach ($entries as $entry) {
            $ret[] = [
                'id' => $entry->id,
                'title' => $entry->title,
                'plugins' => ArrayHelper::getColumn($entry->plugins, 'id'),
                'limit' => $entry->limit,
            ];
        }

        return $ret;
    }

    private function _plugins(): array
    {
        $ret = [];

        $plugins = Plugin::find()
            ->with(['developer', 'categories', 'icon'])
            ->all();

        foreach ($plugins as $plugin) {
            $ret[] = $this->transformPlugin($plugin, false);
        }

        return $ret;
    }
}
