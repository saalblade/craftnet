<?php

namespace craftnet\utilities;

use Craft;
use craft\base\Utility;
use craft\db\Query;

class UnavailablePlugins extends Utility
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('app', 'Unavailable Plugins');
    }

    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'unavailable-plugins';
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
        $plugins = (new Query())
            ->select(['plugin', 'hits'])
            ->from(['craftnet_craft2pluginhits'])
            ->where(['available' => false])
            ->orderBy(['hits' => SORT_DESC])
            ->pairs();

        return Craft::$app->getView()->renderTemplate('craftnet/unavailable-plugins/_content', [
            'plugins' => $plugins,
        ]);
    }
}
