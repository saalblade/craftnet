<?php
/**
 * Craft 2 Plugin Info
 *
 * This array provides the default information to return about Craft 2 plugins,
 * presented in the "Craft 3 Plugin Availability" report on the Updates page in
 * Craft 2.
 *
 * If a plugin isn't available in the store yet and doesn't have any default
 * info listed here, it will be listed as "Not available yet".
 *
 * If a plugin has been added to a developer's account at id.craftcms.com, then
 * it will be listed as either "Available" or "Coming soon", depending on
 * whether it's enabled in the store yet.
 *
 * To customize the info returned for plugins that aren't available yet, add a
 * new item to this array, using the plugin's Craft 2 handle as the key.
 * Possible values are:
 *
 * - handle:      The plugin's new Craft 3 handle. Only set this if the handle
 *                has changed beyond moving from CamelCase to kebab-case.
 * - statusColor: The status color to give the plugin (green, orange, or red).
 * - status:      The status message to give the plugin. This can include
 *                Markdown formatting.
 */

return [
    'Adminbar' => [
        'handle' => 'admin-bar',
    ],
    'AdWizard' => [
        'statusColor' => 'orange',
        'status' => 'Currently in development'
    ],
    'Algolia' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Scout](https://github.com/rias500/craft-scout) could be used instead'
    ],
    'AmCommand' => [
        'handle' => 'command-palette',
    ],
    'TheArchitect' => [
        'handle' => 'architect',
    ],
    'AssetRev' => [
        'handle' => 'assetrev',
    ],
    'BusinessLogic' => [
        'statusColor' => 'red',
        'status' => 'Must be updated manually. Use [pluginfactory.io](https://pluginfactory.io/) to generate a Craft 3 plugin scaffolding.'
    ],
    'CacheFlag' => [
        'statusColor' => 'orange',
        'status' => 'Coming soon'
    ],
    'CodeBlock' => [
        'statusColor' => 'red',
        'status' => 'Discontinued (use [Simple Text](https://github.com/craftcms/simple-text) instead)'
    ],
    'ColorSwatches' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Colour Swatches](https://github.com/rias500/craft-colour-swatches) could be used instead'
    ],
    'CpFieldLinks' => [
        'handle' => 'cp-field-inspect'
    ],
    'DeleteAllEntryVersions' => [
        'handle' => 'delete-entry-versions'
    ],
    'DigitalDownload' => [
        'statusColor' => 'orange',
        'status' => 'Currently in development'
    ],
    'EntriesSubset' => [
        'handle' => 'entriessubset'
    ],
    'FruitLinkIt' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Typed Link Field](https://github.com/sebastian-lenz/craft-linkfield) could be used instead'
    ],
    'Hacksaw' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Typogrify](https://github.com/nystudio107/craft3-typogrify) could be used instead'
    ],
    'Help' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Guide](https://github.com/wbrowar/craft-3-guide) could be used instead'
    ],
    'Hue' => [
        'statusColor' => 'red',
        'status' => 'Unnecessary thanks to [Color field improvements](https://github.com/craftcms/cms/issues/2059)'
    ],
    'Imager' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [ImageOptimize](https://github.com/nystudio107/craft3-imageoptimize) or [Imgix](https://github.com/sjelfull/craft3-imgix) could be used instead'
    ],
    'Inflect' => [
        'statusColor' => 'red',
        'status' => 'Discontinued (see the [readme](https://github.com/lukeholder/craft-inflect/blob/master/readme.md#craft-2-only) for upgrade guidance)'
    ],
    'LanguageLink' => [
        'statusColor' => 'orange',
        'status' => 'Currently in development'
    ],
    'MnBreakAndContinue' => [
        'handle' => 'twig-perversion'
    ],
    'MnEager' => [
        'handle' => 'agnostic-fetch'
    ],
    'MnTwigPerversion' => [
        'handle' => 'twig-perversion'
    ],
    'Moltin' => [
        'statusColor' => 'red',
        'status' => 'Discontinued (see the [readme](https://github.com/lindseydiloreto/craft-moltin) for additional details)'
    ],
    'Oauth' => [
        'statusColor' => 'red',
        'status' => 'Unnecessary thanks to Craft 3’s inclusion of an OAuth 2 client library'
    ],
    'PhpTweak' => [
        'statusColor' => 'red',
        'status' => 'Discontinued (see the [readme](https://github.com/lindseydiloreto/craft-phptweak) for additional details)'
    ],
    'RedactorExtras' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but the new [Redactor](https://github.com/craftcms/redactor) plugin makes it easy to include Redactor plugins without a Craft plugin.'
    ],
    'RedactorI' => [
        'statusColor' => 'red',
        'status' => 'Discontinued (use [Redactor](https://github.com/craftcms/redactor) instead)'
    ],
    'RetconHtml' => [
        'handle' => 'retcon',
        'statusColor' => 'orange',
        'status' => 'Coming soon'
    ],
    'Retour' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Redirect Manager](https://github.com/Dolphiq/craft3-plugin-redirect) could be used instead'
    ],
    'SearchPlus' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Scout](https://github.com/Rias500/craft3-scout) could be used instead'
    ],
    'SimpleMap' => [
        'handle' => 'simplemap',
    ],
    'Sitemap' => [
        'handle' => 'sitemap-tmp',
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [XML Sitemap](https://github.com/Dolphiq/craft3-plugin-sitemap) could be used instead',
    ],
    'SmartMap' => [
        'statusColor' => 'orange',
        'status' => 'Currently in development'
    ],
    'SproutFields' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [NSM Fields](https://github.com/newism/craft3-fields) could be used instead'
    ],
    'StarRatings' => [
        'statusColor' => 'orange',
        'status' => 'Currently in development'
    ],
    'Upvote' => [
        'statusColor' => 'orange',
        'status' => 'Currently in development'
    ],
];
