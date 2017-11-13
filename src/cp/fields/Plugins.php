<?php

namespace craftcom\cp\fields;

use Craft;
use craft\fields\BaseRelationField;
use craftcom\plugins\Plugin;

/**
 * Plugins represents a Plugins field.
 *
 * @package craftcom\cp\fields
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
        return Craft::t('app', 'Add an entry');
    }
}
