<?php

namespace craftnet\fields;

use Craft;
use craft\fields\BaseRelationField;
use craftnet\plugins\Plugin;

/**
 * Plugins represents a Plugins field.
 */
class Plugins extends BaseRelationField
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('app', 'Plugins');
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return Plugin::class;
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('app', 'Add a plugin');
    }
}
