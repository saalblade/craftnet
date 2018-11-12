<?php

namespace craftnet\controllers\api\v1;

use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;
use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\HtmlPurifier;
use craft\models\Update;
use craftnet\controllers\api\BaseApiController;
use craftnet\errors\ValidationException;
use yii\helpers\Markdown;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Class UpdatesController
 */
class UpdatesController extends BaseApiController
{
    public $defaultAction = 'get';

    public function runAction($id, $params = []): Response
    {
        // BC support for old POST /v1/updates requests
        if ($id === 'old') {
            try {
                $payload = $this->getPayload('updates-request-old');
                $headers = Craft::$app->getRequest()->getHeaders();
                $scheme = $payload->request->port === 443 ? 'https' : 'http';
                $port = !in_array($payload->request->port, [80, 443]) ? ":{$payload->request->port}" : '';
                $headers->set('X-Craft-Host', "{$scheme}://{$payload->request->hostname}{$port}");
                $headers->set('X-Craft-User-Ip', $payload->request->ip);
                $headers->set('X-Craft-User-Email', $payload->user->email);
                $platform = [];
                foreach ($payload->platform as $name => $value) {
                    $platform[] = "{$name}:{$value}";
                }
                $headers->set('X-Craft-Platform', implode(',', $platform));
                $headers->set('X-Craft-License', $payload->cms->licenseKey);
                $system = ["craft:{$payload->cms->version};{$payload->cms->edition}"];
                $pluginLicenses = [];
                if (!empty($payload->plugins)) {
                    foreach ($payload->plugins as $pluginHandle => $pluginInfo) {
                        $system[] = "plugin-{$pluginHandle}:{$pluginInfo->version}";
                        if ($pluginInfo->licenseKey !== null) {
                            $pluginLicenses[] = "{$pluginHandle}:{$pluginInfo->licenseKey}";
                        }
                    }
                }
                $headers->set('X-Craft-System', implode(',', $system));
                if (!empty($pluginLicenses)) {
                    $headers->set('X-Craft-Plugin-Licenses', implode(',', $pluginLicenses));
                }
            } catch (ValidationException $e) {
                // let actionGet() throw the validation error
            }
            $id = 'get';
        }

        return parent::runAction($id, $params);
    }

    /**
     * Retrieves available system updates.
     *
     * @return Response
     * @throws \Throwable
     */
    public function actionGet(): Response
    {
        if ($this->cmsVersion === null) {
            throw new BadRequestHttpException('Unable to determine the current Craft version.');
        }

        return $this->asJson([
            'cms' => $this->_getCmsUpdateInfo(),
            'plugins' => $this->_getPluginUpdateInfo(),
        ]);
    }

    /**
     * Returns CMS update info.
     *
     * @return array
     */
    private function _getCmsUpdateInfo(): array
    {
        $info = [
            'status' => Update::STATUS_ELIGIBLE,
            'releases' => $this->_releases('craftcms/cms', $this->cmsVersion),
        ];

        if (!empty($this->cmsLicenses)) {
            $cmsLicense = reset($this->cmsLicenses);
            if ($cmsLicense->expired) {
                $info['status'] = Update::STATUS_EXPIRED;
                $info['renewalUrl'] = 'https://id.craftcms.com/';
            }
        }

        return $info;
    }

    /**
     * Returns plugin update info.
     *
     * @return array
     */
    private function _getPluginUpdateInfo(): array
    {
        $updateInfo = [];

        foreach ($this->plugins as $handle => $plugin) {
            $info = [
                'status' => Update::STATUS_ELIGIBLE,
                'releases' => $this->_releases($plugin->packageName, $this->pluginVersions[$handle]),
            ];

            if (isset($this->pluginLicenses[$handle]) && $this->pluginLicenses[$handle]->expired) {
                $info['status'] = Update::STATUS_EXPIRED;
                $info['renewalUrl'] = 'https://id.craftcms.com';
            }

            $updateInfo[$handle] = $info;
        }

        return $updateInfo;
    }

    /**
     * Transforms releases for inclusion in [[actionIndex()]] response JSON.
     *
     * @param string $name The package name
     * @param string $fromVersion The version that is already installed
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
                        try {
                            $normalizedVersion = $vp->normalize($match[1]);
                        } catch (\UnexpectedValueException $e) {
                            continue;
                        }

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
                $currentReleaseInfo->notes = $this->_parseReleaseNotes($currentNotes);
            }
        }

        // Drop the version keys and convert objects to arrays
        return ArrayHelper::toArray(array_values($releaseInfo));
    }

    /**
     * Parses releases notes into HTML.
     *
     * @param string $notes
     * @return string
     */
    private function _parseReleaseNotes(string $notes): string
    {
        // Parse as Markdown
        $notes = Markdown::process($notes, 'gfm');

        // Purify HTML
        $notes = HtmlPurifier::process($notes);

        // Notes/tips
        $notes = preg_replace('/<blockquote><p>\{(note|tip|warning)\}/', '<blockquote class="note $1"><p>', $notes);

        return $notes;
    }
}
