<?php

namespace craftnet\controllers\api\v1;

use Composer\Semver\VersionParser;
use Craft;
use craft\helpers\ArrayHelper;
use craft\models\Update;
use craftnet\ChangelogParser;
use craftnet\controllers\api\BaseApiController;
use craftnet\errors\ValidationException;
use craftnet\plugins\Plugin;
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
            // Get the latest release that's compatible with their current Craft version
            $toVersion = Plugin::find()
                ->id($plugin->id)
                ->withLatestReleaseInfo(true, $this->cmsVersion)
                ->select(['latestVersion'])
                ->scalar();

            $info = [
                'status' => Update::STATUS_ELIGIBLE,
                'releases' => $toVersion ? $this->_releases($plugin->packageName, $this->pluginVersions[$handle], $toVersion) : [],
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
     * @param string|null $toVersion The version that is available to be installed (if null the latest version will be assumed)
     * @return array
     */
    private function _releases(string $name, string $fromVersion, string $toVersion = null): array
    {
        $packageManager = $this->module->getPackageManager();
        $minStability = VersionParser::parseStability($fromVersion);

        if ($toVersion !== null) {
            $versions = $packageManager->getVersionsBetween($name, $fromVersion, $toVersion, $minStability);
        } else {
            $versions = $packageManager->getVersionsAfter($name, $fromVersion, $minStability);
        }

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
            $changelogReleases = (new ChangelogParser())->parse($changelog, $fromVersion);
            foreach ($changelogReleases as $normalizedVersion => $release) {
                if (isset($releaseInfo[$normalizedVersion])) {
                    $releaseInfo[$normalizedVersion]->critical = $release['critical'];
                    $releaseInfo[$normalizedVersion]->date = $release['date'];
                    $releaseInfo[$normalizedVersion]->notes = $release['notes'];
                }
            }
        }

        // Drop the version keys and convert objects to arrays
        return ArrayHelper::toArray(array_values($releaseInfo));
    }
}
