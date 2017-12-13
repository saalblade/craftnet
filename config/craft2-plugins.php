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
    'CpFieldLinks' => [
        'handle' => 'cp-field-inspect'
    ],
    'DeleteAllEntryVersions' => [
        'handle' => 'delete-entry-versions'
    ],
    'Inflect' => [
        'statusColor' => 'red',
        'status' => 'Craft 2.x and Craft 3 now contain most of the twig filters provided, but [Typogrify](https://github.com/nystudio107/craft3-typogrify) could be used instead.'
    ],
    'Hacksaw' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Typogrify](https://github.com/nystudio107/craft3-typogrify) could be used instead'
    ],
    'Hue' => [
        'statusColor' => 'orange',
        'status' => 'Unnecessary thanks to [Color field improvements](https://github.com/craftcms/cms/issues/2059)'
    ],
    'Imager' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [ImageOptimize](https://github.com/nystudio107/craft3-imageoptimize) or [Imgix](https://github.com/sjelfull/craft3-imgix) could be used instead'
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
    'redactorExtras' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but the new [Redactor](https://github.com/craftcms/redactor) plugin makes it easy to include Redactor plugins without a Craft plugin.'
    ],
    'RedactorI' => [
        'statusColor' => 'orange',
        'status' => 'Discontinued (use [Redactor](https://github.com/craftcms/redactor) instead)'
    ],
    'Retour' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Redirect Manager](https://github.com/Dolphiq/craft3-plugin-redirect) could be used instead'
    ],
    'SearchPlus' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [Scout](https://github.com/Rias500/craft3-scout) could be used instead'
    ],
    'SproutFields' => [
        'statusColor' => 'orange',
        'status' => 'Not available yet, but [NSM Fields](https://github.com/newism/craft3-fields) could be used instead'
    ],
];
