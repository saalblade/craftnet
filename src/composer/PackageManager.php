<?php

namespace craftcom\composer;

use Composer\Repository\PlatformRepository;
use Composer\Semver\Comparator;
use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use Craft;
use craft\db\Query;
use craft\helpers\Db;
use craft\helpers\FileHelper;
use craft\helpers\Json;
use craftcom\composer\jobs\DeletePaths;
use craftcom\composer\jobs\UpdatePackage;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\Console;

class PackageManager extends Component
{
    public function packageExists(string $name): bool
    {
        return (new Query())
            ->from(['craftcom_packages'])
            ->where(['name' => $name])
            ->exists();
    }

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

    public function packageVersionsExist(string $name, array $constraints): bool
    {
        // Get all of the known versions for the package
        $versions = (new Query())
            ->select(['version'])
            ->distinct()
            ->from(['craftcom_packageversions pv'])
            ->innerJoin(['craftcom_packages p'], '[[p.id]] = [[pv.packageId]]')
            ->where(['p.name' => $name])
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

    public function savePackage(Package $package): void
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

    public function removePackage(string $name): void
    {
        Craft::$app->getDb()->createCommand()
            ->delete('craftcom_packages', ['name' => $name])
            ->execute();
    }

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

    private function _createPackageQuery(): Query
    {
        return (new Query())
            ->select(['id', 'name', 'type', 'repository', 'managed', 'latestVersion', 'abandoned', 'replacementPackage'])
            ->from(['craftcom_packages']);
    }

    public function updatePackage(string $name, bool $force = false): void
    {
        $isConsole = Craft::$app->getRequest()->getIsConsoleRequest();
        if ($isConsole) {
            Console::output("Updating version data for {$name}...");
        }

        $package = $this->getPackage($name);
        $vcs = $package->getVcs();

        // Get all of the already known versions
        $storedVersions = (new Query())
            ->select(['id', 'version', 'sha'])
            ->from(['craftcom_packageversions'])
            ->where(['packageId' => $package->id])
            ->indexBy('version')
            ->all();

        // Get the versions from the VCS
        $versionStability = [];
        $versions = array_filter($vcs->getVersions(), function($version) use ($package, &$versionStability) {
            // Don't include development versions, and versions that aren't actually required by any managed packages
            if (($stability = VersionParser::parseStability($version)) === 'dev') {
                return false;
            }
            $versionStability[$version] = $stability;
            return ($package->managed || $this->isDependencyVersionRequired($package->name, $version));
        }, ARRAY_FILTER_USE_KEY);

        // See which already-stored versions have been deleted/updated
        $versionIdsToDelete = [];
        $totalDeleted = 0;
        $totalUpdated = 0;
        foreach ($storedVersions as $version => $info) {
            if ($force || !isset($versions[$version]) || $versions[$version] !== $info['sha']) {
                $versionIdsToDelete[] = $info['id'];
                if (isset($versions[$version])) {
                    unset($storedVersions[$version]);
                    $totalUpdated++;
                } else {
                    $totalDeleted++;
                }
            }
        }

        if ($isConsole) {
            Console::stdout(Console::ansiFormat('- new: ', [Console::FG_YELLOW]));
            Console::output(count($versions) - (count($storedVersions) + $totalUpdated));
            Console::stdout(Console::ansiFormat('- updated: ', [Console::FG_YELLOW]));
            Console::output($totalUpdated);
            Console::stdout(Console::ansiFormat('- deleted: ', [Console::FG_YELLOW]));
            Console::output($totalDeleted);
        }

        $db = Craft::$app->getDb();

        if (!empty($versionIdsToDelete)) {
            if ($isConsole) {
                Console::stdout('Deleting old versions ... ');
            }

            $db->createCommand()
                ->delete('craftcom_packageversions', ['id' => $versionIdsToDelete])
                ->execute();

            if ($isConsole) {
                Console::output('done');
            }
        }

        $totalToProcess = max(count($versions) - count($storedVersions), 0);

        // Bail early if there's nothing new
        if ($totalToProcess === 0) {
            if ($isConsole) {
                Console::output('No new versions to process');
            }
            return;
        }

        if ($isConsole) {
            Console::output('Processing new versions ...');
        }

        // Sort by newest => oldest
        uksort($versions, function(string $version1, string $version2): int {
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

        foreach ($versions as $version => $sha) {
            if (!$foundStable && $versionStability[$version] === 'stable') {
                $latestVersion = $version;
                $foundStable = true;
            } else if ($latestVersion === null) {
                $latestVersion = $version;
            }

            // Skip if we already know about it
            if (isset($storedVersions[$version])) {
                continue;
            }

            if ($isConsole) {
                Console::stdout(Console::ansiFormat("- processing {$version} ({$sha}) ... ", [Console::FG_YELLOW]));
            }

            $packageVersion = new PackageVersion([
                'packageId' => $package->id,
                'version' => $version,
                'sha' => $sha,
            ]);
            $vcs = $package->getVcs();
            $vcs->populateVersion($packageVersion);
            $this->savePackageVersion($package, $packageVersion);

            if (!empty($packageVersion->require)) {
                $depValues = [];
                foreach ($packageVersion->require as $depName => $constraints) {
                    $depValues[] = [$package->id, $packageVersion->id, $depName, $constraints];
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

            if ($isConsole) {
                Console::output(Console::ansiFormat('done', [Console::FG_YELLOW]));
            }
        }

        // Update the package's latestVersion and dateUpdated
        $db->createCommand()
            ->update('craftcom_packages', ['latestVersion' => $latestVersion], ['id' => $package->id])
            ->execute();

        if ($package->pluginId) {
            $db->createCommand()
                ->update('craftcom_plugins', ['latestVersion' => $latestVersion], ['id' => $package->pluginId])
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
                $queue = Craft::$app->getQueue();
                foreach ($depsToUpdate as $depName) {
                    $queue->push(new UpdatePackage([
                        'name' => $depName,
                    ]));
                    if ($isConsole) {
                        Console::output("{$depName} is queued to be updated");
                    }
                }
            }
        }

        if ($isConsole) {
            Console::output("Done processing {$totalToProcess} versions");
        }
    }

    public function savePackageVersion(Package $package, PackageVersion $version): void
    {
        $db = Craft::$app->getDb();
        $db->createCommand()
            ->insert('craftcom_packageversions', [
                'packageId' => $version->packageId,
                'sha' => $version->sha,
                'description' => $version->description,
                'version' => $version->version,
                'normalizedVersion' => (new VersionParser())->normalize($version->version),
                'type' => $version->type,
                'keywords' => $version->keywords ? Json::encode($version->keywords) : null,
                'homepage' => $version->homepage,
                'time' => $version->time,
                'license' => $version->license ? Json::encode($version->license) : null,
                'authors' => $version->authors ? Json::encode($version->authors) : null,
                'support' => $version->support ? Json::encode($version->support) : null,
                'conflict' => $version->conflict ? Json::encode($version->conflict) : null,
                'replace' => $version->replace ? Json::encode($version->replace) : null,
                'provide' => $version->provide ? Json::encode($version->provide) : null,
                'suggest' => $version->suggest ? Json::encode($version->suggest) : null,
                'autoload' => $version->autoload ? Json::encode($version->autoload) : null,
                'includePaths' => $version->includePaths ? Json::encode($version->includePaths) : null,
                'targetDir' => $version->targetDir,
                'extra' => $version->extra ? Json::encode($version->extra) : null,
                'binaries' => $version->binaries ? Json::encode($version->binaries) : null,
                'source' => $version->source ? Json::encode($version->source) : null,
                'dist' => $version->dist ? Json::encode($version->dist) : null,
            ])
            ->execute();
        $version->id = $db->getLastInsertID();
    }

    public function dumpProviderJson()
    {
        // Fetch all the data
        $packages = $this->_createPackageQuery()
            ->select(['id', 'name', 'abandoned', 'replacementPackage'])
            ->where(['not', ['latestVersion' => null]])
            ->indexBy('id')
            ->all();
        $versions = (new Query())
            ->select([
                'id',
                'packageId',
                'description',
                'version',
                'normalizedVersion',
                'type',
                'keywords',
                'homepage',
                'time',
                'license',
                'authors',
                //'support',
                'conflict',
                'replace',
                'provide',
                'suggest',
                'autoload',
                'includePaths',
                'targetDir',
                'extra',
                'binaries',
                //'source',
                'dist',
            ])
            ->from(['craftcom_packageversions'])
            ->indexBy('id')
            ->all();
        $deps = (new Query())
            ->select(['versionId', 'name', 'constraints'])
            ->from(['craftcom_packagedeps'])
            ->all();

        // Assemble the data
        $depsByVersion = [];
        foreach ($deps as $dep) {
            $depsByVersion[$dep['versionId']][] = $dep;
//            $name = $packages[$dep['packageId']]['name'];
//            $version = $versions[$dep['versionId']]['version'];
//            $providers[$name]['packages'][$name][$version]['require'][$dep['name']] = $dep['constraints'];
        }

        $providers = [];

        foreach ($versions as $version) {
            $package = $packages[$version['packageId']];
            $name = $package['name'];

            if (isset($depsByVersion[$version['id']])) {
                $require = [];
                foreach ($depsByVersion[$version['id']] as $dep) {
                    $require[$dep['name']] = $dep['constraints'];
                }
            } else {
                $require = null;
            }

            // Assemble in the same order as \Packagist\WebBundle\Entity\Version::toArray()
            // `support` and `source` are intentionally ignored for now.
            $data = [
                'name' => $name,
                'description' => (string)$version['description'],
                'keywords' => $version['keywords'] ? Json::decode($version['keywords']) : [],
                'homepage' => (string)$version['homepage'],
                'version' => $version['version'],
                'version_normalized' => $version['normalizedVersion'],
                'license' => $version['license'] ? Json::decode($version['license']) : [],
                'authors' => $version['authors'] ? Json::decode($version['authors']) : [],
                'dist' => $version['dist'] ? Json::decode($version['dist']) : null,
                'type' => $version['type'],
            ];

            if ($version['time'] !== null) {
                $data['time'] = $version['time'];
            }
            if ($version['autoload'] !== null) {
                $data['autoload'] = Json::decode($version['autoload']);
            }
            if ($version['extra'] !== null) {
                $data['extra'] = Json::decode($version['extra']);
            }
            if ($version['targetDir'] !== null) {
                $data['target-dir'] = $version['targetDir'];
            }
            if ($version['includePaths'] !== null) {
                $data['include-path'] = $version['includePaths'];
            }
            if ($version['binaries'] !== null) {
                $data['bin'] = Json::decode($version['binaries']);
            }
            if ($require !== null) {
                $data['require'] = $require;
            }
            if ($version['suggest'] !== null) {
                $data['suggest'] = Json::decode($version['suggest']);
            }
            if ($version['conflict'] !== null) {
                $data['conflict'] = Json::decode($version['conflict']);
            }
            if ($version['provide'] !== null) {
                $data['provide'] = Json::decode($version['provide']);
            }
            if ($version['replace'] !== null) {
                $data['replace'] = Json::decode($version['replace']);
            }
            if ($package['abandoned']) {
                $data['abandoned'] = $package['replacementPackage'] ?: true;
            }
            $data['uid'] = (int)$version['id'];

            $providers[$name]['packages'][$name][$version['version']] = $data;
        }

        // Create the JSON files
        $web = getenv('COMPOSER_WEBROOT');
        $oldPaths = [];
        $indexData = [];

        foreach ($providers as $name => $providerData) {
            $providerHash = $this->_writeJsonFile($providerData, "{$web}/p/{$name}/%hash%.json", $oldPaths);
            $indexData['providers'][$name] = ['sha256' => $providerHash];
        }

        $indexPath = 'p/provider/%hash%.json';
        $indexHash = $this->_writeJsonFile($indexData, "{$web}/{$indexPath}", $oldPaths);

        $rootData = [
            'packages' => [],
            'provider-includes' => [
                $indexPath => ['sha256' => $indexHash],
            ],
            'providers-url' => '/p/%package%/%hash%.json',
        ];

        FileHelper::writeToFile("{$web}/packages.json", Json::encode($rootData));

        if (!empty($oldPaths)) {
            Craft::$app->getQueue()->delay(60 * 5)->push(new DeletePaths([
                'paths' => $oldPaths,
            ]));
        }
    }

    /**
     * Writes a new JSON file and returns its hash.
     *
     * @param array  $data     The data to write
     * @param string $path     The path to save the content (can contain a %hash% tag)
     * @param array  $oldPaths Array of existing files that should be deleted
     *
     * @return string
     */
    private function _writeJsonFile(array $data, string $path, &$oldPaths): string
    {
        $content = Json::encode($data);
        $hash = hash('sha256', $content);
        $path = str_replace('%hash%', $hash, $path);

        // If nothing's changed, we're done
        if (file_exists($path)) {
            return $hash;
        }

        // Mark any existing files in there for deletion
        $dir = dirname($path);
        if (is_dir($dir) && ($handle = opendir($dir))) {
            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $oldPaths[] = $dir.'/'.$file;
            }
            closedir($handle);
        }

        // Write the new file
        FileHelper::writeToFile($path, $content);

        return $hash;
    }
}
