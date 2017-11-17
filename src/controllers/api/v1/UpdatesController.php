<?php

namespace craftcom\controllers\api\v1;

use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\Html;
use craftcom\composer\PackageVersion;
use craftcom\controllers\api\BaseApiController;
use craftcom\Module;
use craftcom\plugins\Plugin;
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
        $body = $this->getRequestBody('updates-request');

        return $this->asJson([
            'cms' => $this->_getCmsUpdateInfo($body),
            'plugins' => $this->_getPluginUpdateInfo($body)
        ]);
    }

    /**
     * Returns CMS update info.
     *
     * @param \stdClass $body
     *
     * @return array
     */
    private function _getCmsUpdateInfo(\stdClass $body): array
    {
        $releases = null;
        $stability = VersionParser::parseStability($body->cms->version);
        $latest = Module::getInstance()->getPackageManager()->getLatestVersion('craftcms/cms', $stability);

        if ($latest) {
            $releases = $this->_getReleases($latest, $body->cms->version);
        }

        return [
            'status' => 'eligible',
            'releases' => $releases ?? [],
            //'renewalPrice' => '59',
            //'renewalCurrency' => 'USD',
            //'renewalUrl' => 'dashboard',
        ];
    }

    /**
     * Returns plugin update info.
     *
     * @param \stdClass $body
     *
     * @return array
     */
    private function _getPluginUpdateInfo(\stdClass $body): array
    {
        $updateInfo = [];
        $handles = array_keys(get_object_vars($body->plugins));

        if (!empty($handles)) {
            $packageManager = Module::getInstance()->getPackageManager();

            /** @var Plugin[] $plugins */
            $plugins = Plugin::find()
                ->handle($handles)
                ->indexBy('handle')
                ->all();

            foreach ($body->plugins as $handle => $pluginInfo) {
                $releases = null;
                if (isset($plugins[$handle])) {
                    $stability = VersionParser::parseStability($pluginInfo->version);
                    $latest = $packageManager->getLatestVersion($plugins[$handle]->packageName, $stability);
                    if ($latest) {
                        $releases = $this->_getReleases($latest, $pluginInfo->version);
                    }
                }
                $updateInfo[$handle] = [
                    'status' => 'eligible',
                    'releases' => $releases ?? [],
                ];
            }
        }

        return $updateInfo;
    }

    /**
     * Returns release info based on a given changelog URL.
     *
     * @param PackageVersion $version The package version model
     * @param string         $from    The version that is already installed
     *
     * @return array
     */
    private function _getReleases(PackageVersion $version, string $from): array
    {
        // Make sure they're not already at the target version
        if (Comparator::equalTo($from, $version->version)) {
            return [];
        }

        $releases = [];
        $foundTarget = false;

        if ($version->changelog) {
            // Move it to a temp file & parse it
            $file = tmpfile();
            fwrite($file, $version->changelog);
            fseek($file, 0);

            $currentRelease = null;
            $currentNotes = '';

            while (($line = fgets($file)) !== false) {
                // Is this an H1 or H2?
                if (strncmp($line, '# ', 2) === 0 || strncmp($line, '## ', 3) === 0) {
                    // If we're in the middle of getting a release's notes, finish it off
                    if ($currentRelease !== null) {
                        $currentRelease->notes = $this->_parseReleaseNotes($currentNotes);
                        $currentRelease = null;
                    }

                    // Is it an H2 version heading?
                    if (preg_match('/^## (?:.* )?\[?v?(\d+\.\d+\.\d+(?:\.\d+)?(?:-[0-9A-Za-z-\.]+)?)\]?(?:\(.*?\)|\[.*?\])? - (\d{4}[-\.]\d\d?[-\.]\d\d?)( \[critical\])?/i', $line, $match)) {
                        // Is it > the target version? (e.g. an unreleased version)
                        if (Comparator::greaterThan($match[1], $version->version)) {
                            continue;
                        }

                        // Is it <= the currently-installed version?
                        if (Comparator::lessThanOrEqualTo($match[1], $from)) {
                            break;
                        }

                        // Is this the target version?
                        if (!$foundTarget && Comparator::equalTo($match[1], $version->version)) {
                            $foundTarget = true;
                        }

                        // Prep the new release
                        $date = DateTimeHelper::toDateTime(str_replace('.', '-', $match[2]), true);
                        $currentRelease = $releases[] = (object)[
                            'version' => $match[1],
                            'date' => $date ? $date->format(\DateTime::ATOM) : null,
                            'critical' => !empty($match[3]),
                        ];

                        $currentNotes = '';
                    }
                } else if ($currentRelease !== null) {
                    // Append the line to the current release notes
                    $currentNotes .= $line;
                }
            }

            // Close the temp file
            fclose($file);

            // If we're in the middle of getting a release's notes, finish it off
            if ($currentRelease !== null) {
                $this->_parseReleaseNotes($currentNotes);
            }
        }

        // If we never found the target version, add it to the beginning
        if (!$foundTarget) {
            array_unshift($releases, [
                'version' => $version->version,
                'date' => null,
                'critical' => false,
                'notes' => '',
            ]);
        }

        return ArrayHelper::toArray($releases);
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
