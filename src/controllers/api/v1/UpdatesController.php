<?php

namespace craftcom\controllers\api\v1;

use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\Html;
use craftcom\controllers\api\BaseApiController;
use craftcom\plugins\Plugin;
use yii\base\Exception;
use yii\helpers\Markdown;
use yii\web\Response;

/**
 * Class UpdatesController
 *
 * @package craftcom\controllers\api\v1
 */
class UpdatesController extends BaseApiController
{
    /**
     * Handles /v1/updates requests.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        $payload = $this->getPayload('updates-request');

        if (!empty($payload->cms->licenseKey)) {
            $this->addLogRequestKey($payload->cms->licenseKey);
        }

        if (isset($payload->plugins)) {
            foreach ($payload->plugins as $pluginHandle => $plugin) {
                if (!empty($plugin->licenseKey)) {
                    $this->addLogRequestKey($plugin->licenseKey, $pluginHandle);
                }
            }
        }

        return $this->asJson([
            'cms' => $this->_getCmsUpdateInfo($payload),
            'plugins' => $this->_getPluginUpdateInfo($payload)
        ]);
    }

    /**
     * Returns CMS update info.
     *
     * @param \stdClass $payload
     *
     * @return array
     */
    private function _getCmsUpdateInfo(\stdClass $payload): array
    {
        return [
            'status' => 'eligible',
            'releases' => $this->_releases('craftcms/cms', $payload->cms->version),
            //'renewalPrice' => '59',
            //'renewalCurrency' => 'USD',
            //'renewalUrl' => 'dashboard',
        ];
    }

    /**
     * Returns plugin update info.
     *
     * @param \stdClass $payload
     *
     * @return array
     */
    private function _getPluginUpdateInfo(\stdClass $payload): array
    {
        $updateInfo = [];
        
        if (isset($payload->plugins)) {

            $handles = array_keys(get_object_vars($payload->plugins));

            if (!empty($handles)) {
                $packageManager = $this->module->getPackageManager();

                /** @var Plugin[] $plugins */
                $plugins = Plugin::find()
                    ->handle($handles)
                    ->indexBy('handle')
                    ->all();

                foreach ($payload->plugins as $handle => $pluginInfo) {
                    if ($plugin = $plugins[$handle] ?? null) {
                        $releases = $this->_releases($plugin->packageName, $pluginInfo->version);
                    } else {
                        // We don't have a record of this plugin
                        $releases = [];
                    }
                    $updateInfo[$handle] = [
                        'status' => 'eligible',
                        'releases' => $releases,
                    ];
                }
            }
        }

        return $updateInfo;
    }

    /**
     * Transforms releases for inclusion in [[actionIndex()]] response JSON.
     *
     * @param string $name        The package name
     * @param string $fromVersion The version that is already installed
     *
     * @return array
     */
    private function _releases(string $name, string $fromVersion): array
    {
        $packageManager = $this->module->getPackageManager();
        $minStability = VersionParser::parseStability($fromVersion);
        $versions = $packageManager->getVersionsAfter($name, $fromVersion, $minStability);

        // Are they already at the latest?
        if (empty($versions)) {
            return [];
        }

        // Sort descending
        $versions = array_reverse($versions);

        // Prep the release info
        $releaseInfo = [];
        $vp = new VersionParser();
        foreach ($versions as $version) {
            $normalizedVersion = $vp->normalize($version);
            $releaseInfo[$normalizedVersion] = (object)[
                'version' => $version,
            ];
        }

        // Get the latest release's changelog
        $toVersion = reset($versions);
        $changelog = $packageManager->getRelease($name, $toVersion)->changelog ?? null;

        if ($changelog) {
            // Move it to a temp file & parse it
            $file = tmpfile();
            fwrite($file, $changelog);
            fseek($file, 0);

            $currentReleaseInfo = null;
            $currentNotes = '';

            while (($line = fgets($file)) !== false) {
                // Is this an H1 or H2?
                if (strncmp($line, '# ', 2) === 0 || strncmp($line, '## ', 3) === 0) {
                    // If we're in the middle of getting a release's notes, finish it off
                    if ($currentReleaseInfo !== null) {
                        $currentReleaseInfo->notes = $this->_parseReleaseNotes($currentNotes);
                        $currentReleaseInfo = null;
                    }

                    // Is it an H2 version heading?
                    if (preg_match('/^## (?:.* )?\[?v?(\d+\.\d+\.\d+(?:\.\d+)?(?:-[0-9A-Za-z-\.]+)?)\]?(?:\(.*?\)|\[.*?\])? - (\d{4}[-\.]\d\d?[-\.]\d\d?)( \[critical\])?/i', $line, $match)) {
                        // Make sure this is a version we care about
                        $normalizedVersion = $vp->normalize($match[1]);
                        if (!isset($releaseInfo[$normalizedVersion])) {
                            // Is it <= the currently-installed version?
                            if (Comparator::lessThanOrEqualTo($normalizedVersion, $fromVersion)) {
                                break;
                            }
                            continue;
                        }

                        // Fill in the date/critical bits
                        $currentReleaseInfo = $releaseInfo[$normalizedVersion];
                        $date = DateTimeHelper::toDateTime(str_replace('.', '-', $match[2]), true);
                        $currentReleaseInfo->date = $date ? $date->format(\DateTime::ATOM) : null;
                        $currentReleaseInfo->critical = !empty($match[3]);

                        // Start the release notes
                        $currentNotes = '';
                    }
                } else if ($currentReleaseInfo !== null) {
                    // Append the line to the current release notes
                    $currentNotes .= $line;
                }
            }

            // Close the temp file
            fclose($file);

            // If we're in the middle of getting a release's notes, finish it off
            if ($currentReleaseInfo !== null) {
                $this->_parseReleaseNotes($currentNotes);
            }
        }

        // Drop the version keys and convert objects to arrays
        return ArrayHelper::toArray(array_values($releaseInfo));
    }

    /**
     * Parses releases notes into HTML.
     *
     * @param string $notes
     *
     * @return string
     */
    private function _parseReleaseNotes(string $notes): string
    {
        // Encode any HTML within the notes
        $notes = Html::encode($notes);

        // Except for `> blockquotes`
        $notes = preg_replace('/^(\s*)&gt;/m', '$1>', $notes);

        // Parse as Markdown
        $notes = Markdown::process($notes, 'gfm');

        // Notes/tips
        $notes = preg_replace('/<blockquote><p>\{(note|tip|warning)\}/', '<blockquote class="note $1"><p>', $notes);

        return $notes;
    }
}
