<?php

namespace craftcom\composer\vcs;

use Craft;
use craftcom\composer\Package;
use craftcom\composer\PackageVersion;
use yii\base\Object;

abstract class BaseVcs extends Object implements VcsInterface
{
    /**
     * @var Package
     */
    public $package;

    public function __construct(Package $package, array $config = [])
    {
        $this->package = $package;
        parent::__construct($config);
    }

    protected function populateVersionFromComposerConfig(PackageVersion $version, array $config): bool
    {
        // Make sure the versions line up
        if (isset($config['version']) && $config['version'] !== $version->version) {
            Craft::warning("Ignoring package version {$this->package->name}:{$version->version} due to a version mismatch in composer.json: {$config['version']}", __METHOD__);
            $version->nullify();
            return false;
        }

        if (isset($config['description'])) {
            $version->description = $config['description'];
        }

        if (isset($config['type'])) {
            $version->type = $config['type'];
        }

        if (isset($config['keywords'])) {
            $version->keywords = $config['keywords'];
        }

        if (isset($config['homepage'])) {
            $version->homepage = $config['homepage'];
        }

        if (isset($config['time'])) {
            $version->time = $config['time'];
        }

        if (isset($config['license'])) {
            $version->license = (array)$config['license'];
        }

        if (isset($config['authors'])) {
            $version->authors = $config['authors'];
        }

        if (isset($config['support'])) {
            $version->support = $config['support'];
        }

        if (isset($config['require'])) {
            $version->require = $config['require'];
        }

        if (isset($config['conflict'])) {
            $version->conflict = $config['conflict'];
        }

        if (isset($config['replace'])) {
            $version->replace = $config['replace'];
        }

        if (isset($config['provide'])) {
            $version->provide = $config['provide'];
        }

        if (isset($config['suggest'])) {
            $version->suggest = $config['suggest'];
        }

        if (isset($config['autoload'])) {
            $version->autoload = $config['autoload'];
        }

        if (isset($config['include-path'])) {
            $version->includePaths = $config['include-path'];
        }

        if (isset($config['target-dir'])) {
            $version->targetDir = $config['target-dir'];
        }

        if (isset($config['extra'])) {
            $version->extra = $config['extra'];
        }

        if (isset($config['bin'])) {
            $version->binaries = $config['bin'];
        }

        return true;
    }
}
