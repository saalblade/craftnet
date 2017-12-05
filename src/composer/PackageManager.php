<?php

namespace craftcom\composer;

use Composer\Repository\PlatformRepository;
use Composer\Semver\Comparator;
use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use Craft;
use craft\db\Query;
use craft\helpers\Db;
use craft\helpers\Json;
use craftcom\composer\jobs\UpdatePackage;
use craftcom\errors\MissingTokenException;
use craftcom\errors\VcsException;
use yii\base\Component;
use yii\base\Exception;
use yii\db\Expression;
use yii\helpers\Console;

/**
 * @property null|string $randomGitHubFallbackToken
 */
class PackageManager extends Component
{
    /**
     * @var string[]|null
     */
    public $githubFallbackTokens;

    /**
     * @var bool Whether plugins *must* have VCS tokens
     */
    public $requirePluginVcsTokens = true;

    /**
     *
     */
    public function init()
    {
        parent::init();

        if (is_string($this->githubFallbackTokens)) {
            $this->githubFallbackTokens = array_filter(explode(',', $this->githubFallbackTokens));
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function packageExists(string $name): bool
    {
        return (new Query())
            ->from(['craftcom_packages'])
            ->where(['name' => $name])
            ->exists();
    }

    /**
     * @param string $name
     * @param int    $seconds
     *
     * @return bool
     */
    public function packageUpdatedWithin(string $name, int $seconds): bool
    {
        $timestamp = Db::prepareDateForDb(new \DateTime("-{$seconds} seconds"));
        return (new Query())
            ->from(['craftcom_packages'])
            ->where(['name' => $name])
            ->andWhere('[[dateUpdated]] != [[dateCreated]]')
            ->andWhere(['>=', 'dateUpdated', $timestamp])
            ->exists();
    }

    /**
     * @param string $name
     * @param array  $constraints
     *
     * @return bool
     */
    public function packageVersionsExist(string $name, array $constraints): bool
    {
        // Get all of the known versions for the package
        $versions = (new Query())
            ->select(['version'])
            ->distinct()
            ->from(['craftcom_packageversions pv'])
            ->innerJoin(['craftcom_packages p'], '[[p.id]] = [[pv.packageId]]')
            ->where([
                'p.name' => $name,
                'pv.valid' => true,
            ])
            ->column();

        // Make sure each of the constraints is satisfied by at least one of those versions
        foreach ($constraints as $constraint) {
            $satisfied = false;
            foreach ($versions as $version) {
                if (Semver::satisfies($version, $constraint)) {
                    $satisfied = true;
                    break;
                }
            }
            if (!$satisfied) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $name    The package name
     * @param string $version The package version
     *
     * @return PackageRelease|null
     */
    public function getRelease(string $name, string $version)
    {
        $result = $this->_createReleaseQuery($name, $version)->one();

        if (!$result) {
            return null;
        }

        return new PackageRelease($result);
    }

    /**
     * @param string $name         The package name
     * @param string $minStability The minimum required stability (dev, alpha, beta, RC, or stable)
     * @param bool   $sort         Whether the versions should be sorted
     *
     * @return string[] The known package versions
     */
    public function getAllVersions(string $name, string $minStability = 'stable', bool $sort = true): array
    {
        $allowedStabilities = $this->_allowedStabilities($minStability);

        $versions = (new Query())
            ->select(['version'])
            ->distinct()
            ->from(['craftcom_packageversions pv'])
            ->innerJoin(['craftcom_packages p'], '[[p.id]] = [[pv.packageId]]')
            ->where([
                'p.name' => $name,
                'pv.stability' => $allowedStabilities,
                'pv.valid' => true,
            ])
            ->column();

        if ($sort) {
            $this->_sortVersions($versions);
        }

        return $versions;
    }

    /**
     * @param string $name         The package name
     * @param string $minStability The minimum required stability (dev, alpha, beta, RC, or stable)
     *
     * @return string|null The latest version, or null if none can be found
     */
    public function getLatestVersion(string $name, string $minStability = 'stable')
    {
        // Get all the versions
        $versions = $this->getAllVersions($name, $minStability);

        // Return the last one
        return array_pop($versions);
    }

    /**
     * @param string $name         The package name
     * @param string $minStability The minimum required stability
     *
     * @return PackageRelease|null The latest release, or null if none can be found
     */
    public function getLatestRelease(string $name, string $minStability = 'stable')
    {
        $version = $this->getLatestVersion($name, $minStability);
        return $this->getRelease($name, $version);
    }

    /**
     * Returns all the versions after a given version
     *
     * @param string $name         The package name
     * @param string $from         The version that others should be after
     * @param string $minStability The minimum required stability
     * @param bool   $sort         Whether the versions should be sorted
     *
     * @return string[] The versions after $from, sorted oldest-to-newest
     */
    public function getVersionsAfter(string $name, string $from, string $minStability = 'stable', bool $sort = true): array
    {
        // Get all the versions
        $versions = $this->getAllVersions($name, $minStability, false);

        // Filter out the ones <= $from
        $versions = array_filter($versions, function($version) use ($from) {
            return Comparator::greaterThan($version, $from);
        });

        if ($sort) {
            $this->_sortVersions($versions);
        }

        return $versions;
    }

    /**
     * Returns all the releases after a given version
     *
     * @param string $name         The package name
     * @param string $from         The version that others should be after
     * @param string $minStability The minimum required stability
     *
     * @return PackageRelease[] The releases after $from, sorted oldest-to-newest
     */
    public function getReleasesAfter(string $name, string $from, string $minStability = 'stable'): array
    {
        $versions = $this->getVersionsAfter($name, $from, $minStability, false);
        $results = $this->_createReleaseQuery($name, $versions)->all();
        $releases = [];

        foreach ($results as $result) {
            $releases[] = new PackageRelease($result);
        }

        // Sort them oldest-to-newest
        $this->_sortVersions($releases);

        return $releases;
    }

    /**
     * @param string $minStability The minimum required stability (dev, alpha, beta, RC, or stable)
     *
     * @return string[]
     */
    private function _allowedStabilities(string $minStability = 'stable'): array
    {
        $allowedStabilities = [];
        switch ($minStability) {
            case 'dev':
                $allowedStabilities[] = 'dev';
            // no break
            case 'alpha':
                $allowedStabilities[] = 'alpha';
            // no break
            case 'beta':
                $allowedStabilities[] = 'beta';
            // no break
            case 'RC':
                $allowedStabilities[] = 'RC';
            // no break
            default:
                $allowedStabilities[] = 'stable';
        }

        return $allowedStabilities;
    }

    /**
     * Sorts a given list of versions from oldest => newest
     *
     * @param string[]|PackageRelease[] &$versions
     */
    private function _sortVersions(array &$versions)
    {
        usort($versions, function($a, $b): int {
            if ($a instanceof PackageRelease) {
                $a = $a->version;
            }
            if ($b instanceof PackageRelease) {
                $b = $b->version;
            }

            if (Comparator::equalTo($a, $b)) {
                return 0;
            }
            return Comparator::lessThan($a, $b) ? -1 : 1;
        });
    }

    /**
     * @param string $name    The dependency package name
     * @param string $version The dependency package version
     *
     * @return bool Whether any managed packages require this dependency/version
     */
    public function isDependencyVersionRequired(string $name, string $version): bool
    {
        $constraints = (new Query())
            ->select(['constraints'])
            ->distinct()
            ->from(['craftcom_packagedeps'])
            ->where(['name' => $name])
            ->column();

        foreach ($constraints as $constraint) {
            if (Semver::satisfies($version, $constraint)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Package $package
     */
    public function savePackage(Package $package)
    {
        $data = [
            'name' => $package->name,
            'type' => $package->type,
            'managed' => $package->managed,
            'repository' => $package->repository,
            'abandoned' => $package->abandoned,
            'replacementPackage' => $package->replacementPackage,
        ];

        $db = Craft::$app->getDb();

        if ($package->id === null) {
            $db->createCommand()
                ->insert('craftcom_packages', $data)
                ->execute();
            $package->id = $db->getLastInsertID();
        } else {
            $db->createCommand()
                ->update('craftcom_packages', $data, ['id' => $package->id])
                ->execute();
        }
    }

    /**
     * @param string $name
     */
    public function removePackage(string $name)
    {
        Craft::$app->getDb()->createCommand()
            ->delete('craftcom_packages', ['name' => $name])
            ->execute();
    }

    /**
     * Returns all package names.
     *
     * @return string[]
     */
    public function getPackageNames(): array
    {
        return $this->_createPackageQuery()
            ->select(['name'])
            ->column();
    }

    /**
     * @param string $name
     *
     * @return Package
     * @throws Exception
     */
    public function getPackage(string $name): Package
    {
        $result = $this->_createPackageQuery()
            ->where(['name' => $name])
            ->one();
        if (!$result) {
            throw new Exception('Invalid package name: '.$name);
        }
        return new Package($result);
    }

    /**
     * @param int $id
     *
     * @return Package
     * @throws Exception
     */
    public function getPackageById(int $id): Package
    {
        $result = $this->_createPackageQuery()
            ->where(['id' => $id])
            ->one();
        if (!$result) {
            throw new Exception('Invalid package ID: '.$id);
        }
        return new Package($result);
    }

    /**
     * @param string $url
     *
     * @return string|null
     */
    public function getPackageNameByRepoUrl(string $url)
    {
        return $this->_createPackageQuery()
            ->select(['name'])
            ->where(new Expression('lower([[repository]]) = :url', [':url' => $url]))
            ->scalar();
    }

    /**
     * Creates a VCS webhook for a given package.
     *
     * @param string $name
     * @param bool   $createIfExists
     *
     * @return bool
     * @throws Exception if the package couldn't be found
     */
    public function createWebhook(string $name, bool $createIfExists = true): bool
    {
        $package = $this->getPackage($name);

        // Does the package already have a webhook registered?
        if ($package->webhookId) {
            if (!$createIfExists) {
                return true;
            }

            if ($this->deleteWebhook($name)) {
                $package->webhookId = null;
                $package->webhookSecret = null;
            }
        }

        $package->webhookId = null;
        $package->webhookSecret = Craft::$app->getSecurity()->generateRandomString();

        // Store the secret first so we're ready for the VCS's test hook request
        Craft::$app->getDb()->createCommand()
            ->update(
                '{{%craftcom_packages}}',
                ['webhookSecret' => $package->webhookSecret],
                ['id' => $package->id])
            ->execute();

        try {
            $package->getVcs()->createWebhook();
        } catch (\Throwable $e) {
            Craft::warning("Could not create a webhook for {$package->name}: {$e->getMessage()}", __METHOD__);
            Craft::$app->getErrorHandler()->logException($e->getPrevious() ?? $e);

            // Clear out the secret
            $package->webhookSecret = null;
            Craft::$app->getDb()->createCommand()
                ->update(
                    '{{%craftcom_packages}}',
                    ['webhookSecret' => null],
                    ['id' => $package->id])
                ->execute();

            return false;
        }

        // Store the new ID
        Craft::$app->getDb()->createCommand()
            ->update(
                '{{%craftcom_packages}}',
                ['webhookId' => $package->webhookId],
                ['id' => $package->id])
            ->execute();

        return true;
    }

    /**
     * Deletes a VCS webhook for a given package.
     *
     * @param string $name
     * @param bool   $createIfExists
     *
     * @return bool
     * @throws Exception if the package couldn't be found
     */
    public function deleteWebhook(string $name): bool
    {
        $package = $this->getPackage($name);

        if (!$package->webhookId) {
            return true;
        }

        try {
            $package->getVcs()->deleteWebhook();
        } catch (VcsException $e) {
            Craft::warning("Could not delete a webhook for {$package->name}: {$e->getMessage()}", __METHOD__);
            Craft::$app->getErrorHandler()->logException($e->getPrevious() ?? $e);
            return false;
        }

        // Remove our record of it
        Craft::$app->getDb()->createCommand()
            ->update(
                '{{%craftcom_packages}}',
                [
                    'webhookId' => null,
                    'webhookSecret' => null,
                ],
                ['id' => $package->id])
            ->execute();

        return true;
    }

    /**
     * @param string          $name
     * @param string|string[] $version
     *
     * @return Query
     */
    private function _createReleaseQuery(string $name, $version): Query
    {
        $vp = new VersionParser();

        if (is_array($version)) {
            foreach ($version as $k => $v) {
                $version[$k] = $vp->normalize($v);
            }
        } else {
            $version = $vp->normalize($version);
        }

        return (new Query())
            ->select([
                'pv.id',
                'pv.packageId',
                'pv.sha',
                'pv.description',
                'pv.version',
                'pv.type',
                'pv.keywords',
                'pv.homepage',
                'pv.time',
                'pv.license',
                'pv.authors',
                'pv.support',
                'pv.conflict',
                'pv.replace',
                'pv.provide',
                'pv.suggest',
                'pv.autoload',
                'pv.includePaths',
                'pv.targetDir',
                'pv.extra',
                'pv.binaries',
                'pv.source',
                'pv.dist',
                'pv.changelog',
            ])
            ->from(['craftcom_packageversions pv'])
            ->innerJoin(['craftcom_packages p'], '[[p.id]] = [[pv.packageId]]')
            ->where([
                'p.name' => $name,
                'pv.normalizedVersion' => $version,
                'pv.valid' => true,
            ]);
    }

    /**
     * @param string $name  The Composer package name
     * @param bool   $force Whether to update package releases even if their SHA hasn't changed
     * @param bool   $queue Whether to queue the update
     *
     * @throws MissingTokenException if the package is a plugin, but we don't have a VCS token for it
     */
    public function updatePackage(string $name, bool $force = false, bool $queue = false)
    {
        if ($queue) {
            Craft::$app->getQueue()->push(new UpdatePackage([
                'name' => $name,
                'force' => $force,
            ]));
            return;
        }

        $package = $this->getPackage($name);
        $vcs = $package->getVcs();
        $plugin = $package->getPlugin();
        $db = Craft::$app->getDb();
        $isConsole = Craft::$app->getRequest()->getIsConsoleRequest();

        if ($isConsole) {
            Console::output("Updating version data for {$name}...");
        }

        // Get all of the already known versions (including invalid releases)
        $storedVersionInfo = (new Query())
            ->select(['id', 'version', 'sha'])
            ->from(['craftcom_packageversions'])
            ->where(['packageId' => $package->id])
            ->indexBy('version')
            ->all();

        // Get the versions from the VCS
        $normalizedVersions = [];
        $versionStability = [];

        $vcsVersionShas = array_filter($vcs->getVersions(), function($sha, $version) use ($isConsole, $package, &$normalizedVersions, &$versionStability) {
            // Don't include development versions, and versions that aren't actually required by any managed packages
            if (($stability = VersionParser::parseStability($version)) === 'dev') {
                if ($isConsole) {
                    Console::output(Console::ansiFormat("- skipping {$version} ({$sha}) - dev stability", [Console::FG_RED]));
                }
                return false;
            }

            // Don't include duplicate versions
            try {
                $normalizedVersion = (new VersionParser())->normalize($version);
            } catch (\UnexpectedValueException $e) {
                if ($isConsole) {
                    Console::output(Console::ansiFormat("- skipping {$version} ({$sha}) - invalid version", [Console::FG_RED]));
                }
                return false;
            }

            if (isset($normalizedVersions[$normalizedVersion])) {
                if ($isConsole) {
                    Console::output(Console::ansiFormat("- skipping {$version} ({$sha}) - duplicate version", [Console::FG_RED]));
                }
                return false;
            }
            $normalizedVersions[$normalizedVersion] = true;

            $versionStability[$version] = $stability;
            if (!$package->managed && !$this->isDependencyVersionRequired($package->name, $version)) {
                if ($isConsole) {
                    Console::output(Console::ansiFormat("- skipping {$version} ({$sha}) - not required", [Console::FG_RED]));
                }
                return false;
            }

            return true;
        }, ARRAY_FILTER_USE_BOTH);

        // See which already-stored versions have been deleted/updated
        $storedVersions = array_keys($storedVersionInfo);
        $vcsVersions = array_keys($vcsVersionShas);

        $deletedVersions = array_diff($storedVersions, $vcsVersions);
        $newVersions = array_diff($vcsVersions, $storedVersions);
        $updatedVersions = [];

        foreach (array_intersect($storedVersions, $vcsVersions) as $version) {
            if ($force || $storedVersionInfo[$version]['sha'] !== $vcsVersionShas[$version]) {
                $updatedVersions[] = $version;
            }
        }

        if ($isConsole) {
            Console::stdout(Console::ansiFormat('- new: ', [Console::FG_YELLOW]));
            Console::output(count($newVersions));
            Console::stdout(Console::ansiFormat('- updated: ', [Console::FG_YELLOW]));
            Console::output(count($updatedVersions));
            Console::stdout(Console::ansiFormat('- deleted: ', [Console::FG_YELLOW]));
            Console::output(count($deletedVersions));
        }

        if (!empty($deletedVersions) || !empty($updatedVersions)) {
            if ($isConsole) {
                Console::stdout('Deleting old versions ... ');
            }

            $versionIdsToDelete = [];
            foreach (array_merge($deletedVersions, $updatedVersions) as $version) {
                $versionIdsToDelete[] = $storedVersionInfo[$version]['id'];
            }

            $db->createCommand()
                ->delete('craftcom_packageversions', ['id' => $versionIdsToDelete])
                ->execute();

            if ($isConsole) {
                Console::output('done');
            }
        }

        // We can treat "updated" versions as "new" now.
        $newVersions = array_merge($updatedVersions, $newVersions);

        // Bail early if there's nothing new
        if (empty($newVersions)) {
            if ($isConsole) {
                Console::output('No new versions to process');
            }
            return;
        }

        if ($isConsole) {
            Console::output('Processing new versions ...');
        }

        // Sort by newest => oldest
        usort($newVersions, function(string $version1, string $version2): int {
            if (Comparator::lessThan($version1, $version2)) {
                return 1;
            }
            if (Comparator::equalTo($version1, $version2)) {
                return 0;
            }
            return -1;
        });

        $packageDeps = [];
        $latestVersion = null;
        $foundStable = false;

        foreach ($newVersions as $version) {
            $sha = $vcsVersionShas[$version];

            if ($isConsole) {
                Console::stdout(Console::ansiFormat("- processing {$version} ({$sha}) ... ", [Console::FG_YELLOW]));
            }

            $release = new PackageRelease([
                'packageId' => $package->id,
                'version' => $version,
                'sha' => $sha,
            ]);

            $vcs->populateRelease($release);

            if ($isConsole && !$release->valid) {
                Console::stdout(Console::ansiFormat('invalid'.($release->invalidReason ? " ({$release->invalidReason})" : ''), [Console::FG_RED]));
                Console::stdout(Console::ansiFormat(' ... ', [Console::FG_YELLOW]));
            }

            $this->savePackageRelease($release);

            if (!empty($release->require)) {
                $depValues = [];
                foreach ($release->require as $depName => $constraints) {
                    $depValues[] = [$package->id, $release->id, $depName, $constraints];
                    if (
                        $depName !== '__root__' &&
                        $depName !== 'composer-plugin-api' &&
                        !preg_match(PlatformRepository::PLATFORM_PACKAGE_REGEX, $depName) &&
                        strpos($depName, 'bower-asset/') !== 0 &&
                        strpos($depName, 'npm-asset/') !== 0
                    ) {
                        $packageDeps[$depName][$constraints] = true;
                    }
                }
                $db->createCommand()
                    ->batchInsert('craftcom_packagedeps', ['packageId', 'versionId', 'name', 'constraints'], $depValues)
                    ->execute();
            }

            if ($release->valid) {
                if (!$foundStable && $versionStability[$version] === 'stable') {
                    $latestVersion = $version;
                    $foundStable = true;
                } else if ($latestVersion === null) {
                    $latestVersion = $version;
                }
            }

            if ($isConsole) {
                Console::output(Console::ansiFormat('done', [Console::FG_YELLOW]));
            }
        }

        // Update the package's latestVersion and dateUpdated
        $db->createCommand()
            ->update('craftcom_packages', ['latestVersion' => $latestVersion], ['id' => $package->id])
            ->execute();

        if ($plugin && $latestVersion !== $plugin->latestVersion) {
            $plugin->latestVersion = $latestVersion;
            $db->createCommand()
                ->update('craftcom_plugins', ['latestVersion' => $latestVersion], ['id' => $plugin->id])
                ->execute();
        }

        // For each dependency, see if we already have a version that satisfies the conditions
        if (!empty($packageDeps)) {
            $depsToUpdate = [];
            foreach ($packageDeps as $depName => $depVersions) {
                $update = false;
                if (!$this->packageExists($depName)) {
                    if ($isConsole) {
                        Console::stdout("Adding dependency {$depName} ... ");
                    }
                    $this->savePackage(new Package([
                        'name' => $depName,
                        'type' => 'library',
                        'managed' => false,
                    ]));
                    if ($isConsole) {
                        Console::output('done');
                    }
                    $update = true;
                } else if (!$this->packageVersionsExist($depName, array_keys($depVersions))) {
                    $update = true;
                }
                if ($update) {
                    $depsToUpdate[] = $depName;
                }
            }

            if (!empty($depsToUpdate)) {
                foreach ($depsToUpdate as $depName) {
                    $this->updatePackage($depName, $force, true);
                    if ($isConsole) {
                        Console::output("{$depName} is queued to be updated");
                    }
                }
            }
        }

        if ($isConsole) {
            Console::output('Done processing '.count($newVersions).' versions');
        }
    }

    /**
     * @param PackageRelease $release
     */
    public function savePackageRelease(PackageRelease $release)
    {
        $db = Craft::$app->getDb();
        $db->createCommand()
            ->insert('craftcom_packageversions', [
                'packageId' => $release->packageId,
                'sha' => $release->sha,
                'description' => $release->description,
                'version' => $release->version,
                'normalizedVersion' => $release->getNormalizedVersion(),
                'stability' => $release->getStability(),
                'type' => $release->type,
                'keywords' => $release->keywords ? Json::encode($release->keywords) : null,
                'homepage' => $release->homepage,
                'time' => $release->time,
                'license' => $release->license ? Json::encode($release->license) : null,
                'authors' => $release->authors ? Json::encode($release->authors) : null,
                'support' => $release->support ? Json::encode($release->support) : null,
                'conflict' => $release->conflict ? Json::encode($release->conflict) : null,
                'replace' => $release->replace ? Json::encode($release->replace) : null,
                'provide' => $release->provide ? Json::encode($release->provide) : null,
                'suggest' => $release->suggest ? Json::encode($release->suggest) : null,
                'autoload' => $release->autoload ? Json::encode($release->autoload) : null,
                'includePaths' => $release->includePaths ? Json::encode($release->includePaths) : null,
                'targetDir' => $release->targetDir,
                'extra' => $release->extra ? Json::encode($release->extra) : null,
                'binaries' => $release->binaries ? Json::encode($release->binaries) : null,
                'source' => $release->source ? Json::encode($release->source) : null,
                'dist' => $release->dist ? Json::encode($release->dist) : null,
                'changelog' => $release->changelog,
                'valid' => $release->valid,
            ])
            ->execute();
        $release->id = $db->getLastInsertID();
    }

    /**
     * Updates all of the unmanaged package dependencies.
     *
     * @param bool $force Whether to update package releases even if their SHA hasn't changed
     * @param bool $queue Whether to queue the updates
     */
    public function updateDeps(bool $force = false, bool $queue = false)
    {
        Craft::info('Starting to update package dependencies.', __METHOD__);

        $names = $this->_createPackageQuery()
            ->select(['name'])
            ->where(['managed' => false])
            ->column();

        foreach ($names as $name) {
            $this->updatePackage($name, $force, $queue);
        }

        Craft::info('Done updating package dependencies.', __METHOD__);
    }

    /**
     * Returns a random fallback GitHub API token.
     *
     * @return string|null
     */
    public function getRandomGitHubFallbackToken()
    {
        if (empty($this->githubFallbackTokens)) {
            return null;
        }

        $key = array_rand($this->githubFallbackTokens);
        return $this->githubFallbackTokens[$key];
    }

    /**
     * @return Query
     */
    private function _createPackageQuery(): Query
    {
        return (new Query())
            ->select(['id', 'name', 'type', 'repository', 'managed', 'latestVersion', 'abandoned', 'replacementPackage', 'webhookId', 'webhookSecret'])
            ->from(['craftcom_packages']);
    }
}
