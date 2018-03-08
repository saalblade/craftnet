<?php

namespace craftcom\controllers\api\v1;

use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;
use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craft\helpers\Html;
use craft\models\Update;
use craftcom\cms\CmsLicense;
use craftcom\controllers\api\BaseApiController;
use craftcom\errors\LicenseNotFoundException;
use craftcom\errors\ValidationException;
use craftcom\plugins\Plugin;
use yii\base\Exception;
use yii\db\Expression;
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
     * @throws ValidationException
     * @throws \Throwable
     */
    public function actionIndex(): Response
    {
        $payload = $this->getPayload('updates-request');

        $this->addLogRequestKey($payload->cms->licenseKey);

        if (isset($payload->plugins)) {
            foreach ($payload->plugins as $pluginHandle => $plugin) {
                if (!empty($plugin->licenseKey)) {
                    $this->addLogRequestKey($plugin->licenseKey, $pluginHandle);
                }
            }
        }

        try {
            $cmsLicense = $this->module->getCmsLicenseManager()->getLicenseByKey($payload->cms->licenseKey);
        } catch (LicenseNotFoundException $e) {
            throw new ValidationException([
                [
                    'param' => 'cms.licensekey',
                    'message' => $e->getMessage(),
                    'code' => self::ERROR_CODE_MISSING,
                ]
            ]);
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        $errors = [];

        try {
            $cmsUpdates = $this->_getCmsUpdateInfo($payload, $cmsLicense, $errors);
            $pluginUpdates = $this->_getPluginUpdateInfo($payload, $cmsLicense, $errors);

            if (!empty($errors)) {
                throw new ValidationException($errors);
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            // Roll back any license changes we've made
            $transaction->rollBack();
            throw $e;
        }

        return $this->asJson([
            'cms' => $cmsUpdates,
            'plugins' => $pluginUpdates,
        ]);
    }

    /**
     * Returns CMS update info.
     *
     * @param \stdClass $payload
     * @param CmsLicense $cmsLicense
     * @param array $errors
     * @return array|null
     * @throws Exception
     */
    private function _getCmsUpdateInfo(\stdClass $payload, CmsLicense $cmsLicense, array &$errors)
    {
        // make sure that the license is being used on the right domain
        $licenseManager = $this->module->getCmsLicenseManager();
        $domain = $licenseManager->normalizeDomain($payload->request->hostname);

        if ($domain !== null && $domain !== $cmsLicense->domain) {
            if ($cmsLicense->domain) {
                $errors[] = [
                    'param' => 'request.hostname',
                    'message' => "This license can only be used on {$cmsLicense->domain}.",
                    'code' => self::ERROR_CODE_INVALID,
                ];
                return null;
            }

            $cmsLicense->domain = $domain;
            if (!$licenseManager->saveLicense($cmsLicense)) {
                throw new Exception("Could not associate Craft license {$cmsLicense->key} with domain {$domain}.");
            }
        }

        if ($cmsLicense->expired) {
            $status = Update::STATUS_EXPIRED;
        } else {
            $status = Update::STATUS_ELIGIBLE;
        }

        return [
            'status' => $status,
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
     * @param CmsLicense $cmsLicense
     * @param array $errors
     * @return array|null
     * @throws Exception
     */
    private function _getPluginUpdateInfo(\stdClass $payload, CmsLicense $cmsLicense, array &$errors)
    {
        $updateInfo = [];
        $db = Craft::$app->getDb();
        $licenseManager = $this->module->getPluginLicenseManager();

        // Delete any installedplugins rows where lastActivity > 30 days ago
        $db->createCommand()
            ->delete('craftcom_installedplugins', [
                'and',
                ['craftLicenseKey' => $payload->cms->licenseKey],
                ['<', 'lastActivity', Db::prepareDateForDb(new \DateTime('-30 days'))],
            ])
            ->execute();

        if (isset($payload->plugins)) {
            $handles = array_keys(get_object_vars($payload->plugins));

            if (!empty($handles)) {
                /** @var Plugin[] $plugins */
                $plugins = Plugin::find()
                    ->handle($handles)
                    ->indexBy('handle')
                    ->all();

                foreach ($payload->plugins as $handle => $pluginInfo) {
                    if ($plugin = $plugins[$handle] ?? null) {
                        if (!empty($pluginInfo->licenseKey)) {
                            try {
                                $license = $licenseManager->getLicenseByKey($pluginInfo->licenseKey);
                            } catch (LicenseNotFoundException $e) {
                                $errors[] = [
                                    'param' => "plugins.{$handle}.licenseKey",
                                    'message' => $e->getMessage(),
                                    'code' => self::ERROR_CODE_MISSING,
                                ];
                                continue;
                            }

                            if ($license->cmsLicenseId != $cmsLicense->id) {
                                if ($license->cmsLicenseId) {
                                    $errors[] = [
                                        'param' => "plugins.{$handle}.licenseKey",
                                        'message' => 'This license is for use with a different Craft license.',
                                        'code' => self::ERROR_CODE_INVALID,
                                    ];
                                    continue;
                                }

                                $license->cmsLicenseId = $license->id;
                                if (!$licenseManager->saveLicense($license)) {
                                    throw new Exception("Could not associate plugin license {$license->key} with Craft license {$cmsLicense->key}.");
                                }
                            }
                        }

                        if ($license->expired) {
                            $status = Update::STATUS_EXPIRED;
                        } else {
                            $status = Update::STATUS_ELIGIBLE;
                        }

                        $releases = $this->_releases($plugin->packageName, $pluginInfo->version);

                        // Log it
                        $db->createCommand()
                            ->upsert('craftcom_installedplugins', [
                                'craftLicenseKey' => $payload->cms->licenseKey,
                                'pluginId' => $plugin->id,
                            ], [
                                'lastActivity' => Db::prepareDateForDb(new \DateTime()),
                            ], false)
                            ->execute();

                        // Update the plugin's active installs count
                        $db->createCommand()
                            ->update('craftcom_plugins', [
                                'activeInstalls' => new Expression('(select count(*) from [[craftcom_installedplugins]] where [[pluginId]] = :pluginId)', ['pluginId' => $plugin->id]),
                            ], [
                                'id' => $plugin->id,
                            ])
                            ->execute();
                    } else {
                        // We don't have a record of this plugin
                        $status = Update::STATUS_ELIGIBLE;
                        $releases = [];
                    }

                    $updateInfo[$handle] = [
                        'status' => $status,
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
