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
    'AuditLog' => [
        'statusColor' => 'red',
        'status' => 'Discontinued (use [Audit](https://github.com/sjelfull/craft-audit) instead)'
    ],
    'BusinessLogic' => [
        'statusColor' => 'red',
        'status' => 'Must be updated manually. Use [pluginfactory.io](https://pluginfactory.io/) to generate a Craft 3 plugin scaffolding.'
    ],
    'Calendars' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Solspace Calendar](https://solspace.com/craft/calendar/) could be used instead.'
    ],
    'Charge' => [
        'statusColor' => 'orange',
        'status' => 'Currently in development.'
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
    'DuplicateUserDashboard' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Default Dashboard](https://github.com/verbb/default-dashboard) could be used instead.'
    ],
    'EmbeddedAssets' => [
        'handle' => 'embeddedassets',
    ],
    'Entitle' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Wordsmith](https://wordsmith.docs.topshelfcraft.com/guide/casing.html#apTitleize) could be used instead.'
    ],
    'EntriesSubset' => [
        'handle' => 'entriessubset'
    ],
    'FruitLinkIt' => [
        'handle' => 'linkit',
    ],
    'Hacksaw' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Wordsmith](https://wordsmith.docs.topshelfcraft.com/guide/truncation.html#hacksaw) or [Typogrify](https://github.com/nystudio107/craft3-typogrify) could be used instead.'
    ],
    'Help' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Guide](https://github.com/wbrowar/craft-3-guide) could be used instead'
    ],
    'Hue' => [
        'statusColor' => 'red',
        'status' => 'Unnecessary thanks to [Color field improvements](https://github.com/craftcms/cms/issues/2059).'
    ],
    'Imager' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [ImageOptimize](https://github.com/nystudio107/craft3-imageoptimize) or [Imgix](https://github.com/sjelfull/craft3-imgix) could be used instead'
    ],
    'Import' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Feed Me](https://github.com/verbb/feed-me) or [Sprout Import](https://github.com/barrelstrength/craft-sprout-import) could be used instead'
    ],
    'Inflect' => [
        'statusColor' => 'orange',
        'status' => '[Discontinued](https://github.com/lukeholder/craft-inflect/blob/master/readme.md#craft-2-only), but [Typogrify](https://github.com/nystudio107/craft3-typogrify) or [Wordsmith](https://wordsmith.docs.topshelfcraft.com/guide/inflection.html) could be used instead.'
    ],
    'Inflector' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Wordsmith](https://wordsmith.docs.topshelfcraft.com/guide/inflection.html) could be used instead.'
    ],
    'LanguageLink' => [
        'statusColor' => 'green',
        'status' => 'Renamed to "Site Switcher" in Craft 3'
    ],
    'LinkVault' => [
        'statusColor' => 'orange',
        'status' => 'Currently in development'
    ],
    'Maps' => [
        'statusColor' => 'orange',
        'status' => 'Craft 3 upgrade path will be available via [Smart Map](https://www.doublesecretagency.com/plugins/smart-map).'
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
    'Neo' => [
        'statusColor' => 'orange',
        'status' => 'Currently in development',
    ],
    'NpEditMultipleElements' => [
        'handle' => 'sequential-edit',
    ],
    'Oauth' => [
        'statusColor' => 'red',
        'status' => 'Unnecessary thanks to Craft 3’s inclusion of an OAuth 2 client library'
    ],
    'OneDashboard' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Default Dashboard](https://github.com/verbb/default-dashboard) could be used instead.'
    ],
    'PhpTweak' => [
        'statusColor' => 'red',
        'status' => 'Discontinued (see the [readme](https://github.com/lindseydiloreto/craft-phptweak) for additional details)'
    ],
    'PimpMyMatrix' => [
        'handle' => 'spoon',
    ],
    'Printmaker' => [
        'statusColor' => 'orange',
        'status' => 'Currently in development.'
    ],
    'ReadTime' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Wordsmith](https://wordsmith.docs.topshelfcraft.com/guide/statistics.html#readTime) could be used instead.'
    ],
    'RedactorInlineStyles' => [
        'handle' => 'redactor-custom-styles'
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
        'status' => 'Not available yet, but [Redirect Manager](https://github.com/Dolphiq/craft3-plugin-redirect) or [Sprout SEO](https://github.com/barrelstrength/craft-sprout-seo) could be used instead'
    ],
    'Scraper' => [
        'statusColor' => 'orange',
        'status' => 'Currently in development.'
    ],
    'SearchPlus' => [
        'statusColor' => 'orange',
        'status' => 'Currently in development.'
    ],
    'Shortlist' => [
        'statusColor' => 'orange',
        'status' => 'Currently in development.'
    ],
    'SmartyPants' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Typogrify](https://github.com/nystudio107/craft3-typogrify) or [Wordsmith](https://wordsmith.docs.topshelfcraft.com/guide/typography.html#smartypants) could be used instead.'
    ],
    'SimpleMap' => [
        'handle' => 'simplemap',
    ],
    'Sitemap' => [
        'handle' => 'sitemap-tmp',
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [XML Sitemap](https://github.com/Dolphiq/craft3-plugin-sitemap) or [Sprout SEO](https://github.com/barrelstrength/craft-sprout-seo) could be used instead',
    ],
    'SproutInvisibleCaptcha' => [
        'statusColor' => 'orange',
        'status' => 'Features have been rolled into Sprout Forms which is available in the Plugin Store. Invisible Captcha will no longer be a standalone plugin in Craft 3.'
    ],
    'SproutEmail' => [
        'statusColor' => 'orange',
        'status' => 'Currently in development'
    ],
    'SproutSeo' => [
        'statusColor' => 'orange',
        'status' => 'Currently in development'
    ],
    'SquareBitMaps' => [
        'statusColor' => 'orange',
        'status' => 'Craft 3 upgrade path will be available via [Smart Map](https://www.doublesecretagency.com/plugins/smart-map).'
    ],
    'SuperSort' => [
        'handle' => 'supersort',
    ],
    'Widont' => [
        'handle' => 'widontextension',
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Wordsmith](https://wordsmith.docs.topshelfcraft.com/guide/typography.html#widont) could be used instead.'
    ],
];
