<?php

namespace craftcom\composer\vcs;

use Craft;
use craftcom\composer\Package;
use craftcom\composer\PackageRelease;
use yii\base\Object;

abstract class BaseVcs extends Object implements VcsInterface
{
    /**
     * @var Package
     */
    public $package;

    /**
     * BaseVcs constructor.
     *
     * @param Package $package
     * @param array   $config
     */
    public function __construct(Package $package, array $config = [])
    {
        $this->package = $package;
        parent::__construct($config);
    }

    /**
     * @param PackageRelease $release
     * @param array          $config
     *
     * @return bool
     */
    protected function populateReleaseFromComposerConfig(PackageRelease $release, array $config): bool
    {
        // Make sure the versions line up
        if (isset($config['version']) && $config['version'] !== $release->version) {
            Craft::warning("Ignoring package version {$this->package->name}:{$release->version} due to a version mismatch in composer.json: {$config['version']}", __METHOD__);
            $release->nullify();
            return false;
        }

        if (isset($config['description'])) {
            $release->description = $config['description'];
        }

        if (isset($config['type'])) {
            $release->type = $config['type'];
        }

        if (isset($config['keywords'])) {
            $release->keywords = $config['keywords'];
        }

        if (isset($config['homepage'])) {
            $release->homepage = $config['homepage'];
        }

        if (isset($config['time'])) {
            $release->time = $config['time'];
        }

        if (isset($config['license'])) {
            $release->license = (array)$config['license'];
        }

        if (isset($config['authors'])) {
            $release->authors = $config['authors'];
        }

        if (isset($config['support'])) {
            $release->support = $config['support'];
        }

        if (isset($config['require'])) {
            $release->require = $config['require'];
        }

        if (isset($config['conflict'])) {
            $release->conflict = $config['conflict'];
        }

        if (isset($config['replace'])) {
            $release->replace = $config['replace'];
        }

        if (isset($config['provide'])) {
            $release->provide = $config['provide'];
        }

        if (isset($config['suggest'])) {
            $release->suggest = $config['suggest'];
        }

        if (isset($config['autoload'])) {
            $release->autoload = $config['autoload'];
        }

        if (isset($config['include-path'])) {
            $release->includePaths = $config['include-path'];
        }

        if (isset($config['target-dir'])) {
            $release->targetDir = $config['target-dir'];
        }

        if (isset($config['extra'])) {
            $release->extra = $config['extra'];
        }

        if (isset($config['bin'])) {
            $release->binaries = $config['bin'];
        }

        return true;
    }
}
