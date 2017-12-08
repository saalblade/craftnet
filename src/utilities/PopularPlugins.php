<?php
namespace craftcom\utilities;

use Craft;
use craft\base\Utility;
use craft\db\Query;
use craftcom\plugins\Plugin;

class PopularPlugins extends Utility
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('app', 'Popular Plugins');
    }

    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'popular-plugins';
    }

    /**
     * @inheritdoc
     */
    public static function iconPath()
    {
        return Craft::getAlias('@app/icons/plugin.svg');
    }

    /**
     * @inheritdoc
     */
    public static function contentHtml(): string
    {
        $plugins = Plugin::find()
            ->andWhere(['not', ['craftcom_plugins.activeInstalls' => 0]])
            ->orderBy(['activeInstalls' => SORT_DESC])
            ->limit(100)
            ->with(['developer'])
            ->all();

        return Craft::$app->getView()->renderTemplate('craftcom/popular-plugins/_content', [
            'plugins' => $plugins,
        ]);
    }
}
