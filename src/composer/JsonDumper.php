<?php

namespace craftnet\composer;

use Craft;
use craft\db\Query;
use craft\helpers\Console;
use craft\helpers\FileHelper;
use craft\helpers\Json;
use craftnet\composer\jobs\DeletePaths;
use craftnet\composer\jobs\DumpJson;
use yii\base\Component;

/**
 * Composer repository JSON generator
 */
class JsonDumper extends Component
{
    /**
     * @var string The path that packages.json, etc., should be saved
     * @see dumpProviderJson()
     */
    public $composerWebroot;

    /**
     * Dumps out packages.json, and all the provider JSON files.
     *
     * @param bool $queue Whether to queue the dump
     */
    public function dump(bool $queue = false)
    {
        if ($queue) {
            Craft::$app->getQueue()->push(new DumpJson());
            return;
        }

        $isConsole = Craft::$app->getRequest()->getIsConsoleRequest();

        Craft::info('Dumping JSON.', __METHOD__);

        if ($isConsole) {
            Console::stdout('Dumping JSON ... ');
        }

        // Fetch all the data
        $packages = (new Query())
            ->select(['id', 'name', 'abandoned', 'replacementPackage'])
            ->from(['craftnet_packages'])
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
                'support',
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
            ->from(['craftnet_packageversions'])
            ->where([
                'packageId' => array_keys($packages),
                'valid' => true,
            ])
            ->indexBy('id')
            ->all();

        $deps = (new Query())
            ->select(['versionId', 'name', 'constraints'])
            ->from(['craftnet_packagedeps'])
            ->all();

        // Assemble the data
        $depsByVersion = [];
        foreach ($deps as $dep) {
            $depsByVersion[$dep['versionId']][] = $dep;
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
            // `source` is intentionally ignored for now.
            $data = [
                'name' => $name,
                'description' => (string)$version['description'],
                'keywords' => $version['keywords'] ? Json::decode($version['keywords']) : [],
                'homepage' => (string)$version['homepage'],
                'version' => $version['version'],
                'version_normalized' => $version['normalizedVersion'],
                'license' => $version['license'] ? Json::decode($version['license']) : [],
                'authors' => $version['authors'] ? Json::decode($version['authors']) : [],
                'support' => $version['support'] ? Json::decode($version['support']) : [],
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
                $data['include-path'] = Json::decode($version['includePaths']);
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

            if (!empty($data['provide'])) {
                foreach (array_keys($data['provide']) as $provideName) {
                    $providers[$provideName]['packages'][$name][$version['version']] = $data;
                }
            }
        }

        // Create the JSON files
        $oldPaths = [];
        $indexData = [];

        foreach ($providers as $name => $providerData) {
            $providerHash = $this->_writeJsonFile($providerData, "p/{$name}/%hash%.json", $oldPaths);
            $indexData['providers'][$name] = ['sha256' => $providerHash];
        }

        $indexPath = 'p/provider/%hash%.json';
        $indexHash = $this->_writeJsonFile($indexData, $indexPath, $oldPaths);

        $rootData = [
            'packages' => [],
            'provider-includes' => [
                $indexPath => ['sha256' => $indexHash],
            ],
            'providers-url' => "/p/%package%/%hash%.json",
        ];

        Craft::info("Writing JSON file to {$this->composerWebroot}/packages.json", __METHOD__);
        FileHelper::writeToFile($this->composerWebroot.'/packages.json', Json::encode($rootData));

        if ($isConsole) {
            Console::output('done');
        }

//        if (!empty($oldPaths)) {
//            Craft::$app->getQueue()->delay(60 * 5)->push(new DeletePaths([
//                'paths' => $oldPaths,
//            ]));
//        }
    }

    /**
     * Writes a new JSON file and returns its hash.
     *
     * @param array $data The data to write
     * @param string $path The path to save the content (can contain a %hash% tag)
     * @param array $oldPaths Array of existing files that should be deleted
     *
     * @return string
     */
    private function _writeJsonFile(array $data, string $path, &$oldPaths): string
    {
        $content = Json::encode($data);
        $hash = hash('sha256', $content);
        $path = $this->composerWebroot.'/'.str_replace('%hash%', $hash, $path);

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

        Craft::info("Writing JSON file to ".$path, __METHOD__);
        try {
            // Write the new file
            FileHelper::writeToFile($path, $content);
        } catch (\Throwable $throwable) {
            Craft::error($throwable->getMessage(), __METHOD__);
        }

        return $hash;
    }
}
