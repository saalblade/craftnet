<?php

namespace craftnet\composer\vcs;

use Composer\Semver\VersionParser;
use Craft;
use craftnet\composer\Package;
use craftnet\composer\PackageRelease;
use yii\base\BaseObject;

abstract class BaseVcs extends BaseObject implements VcsInterface
{
    /**
     * @var Package
     */
    public $package;

    /**
     * BaseVcs constructor.
     *
     * @param Package $package
     * @param array $config
     */
    public function __construct(Package $package, array $config = [])
    {
        $this->package = $package;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function getChangelogUrl()
    {
        return null;
    }

    /**
     * Cleans up a given version tag.
     *
     * @param string $tag
     *
     * @return string
     */
    protected function cleanTag(string $tag): string
    {
        // Strip the 'release-' prefix from the version if present
        return preg_replace('/^release-/', '', $tag);
    }

    /**
     * @param PackageRelease $release
     * @param array $config
     *
     * @return bool
     */
    protected function populateReleaseFromComposerConfig(PackageRelease $release, array $config): bool
    {
        // Make sure the versions line up
        if (isset($config['version'])) {
            $normalized = (new VersionParser())->normalize($config['version']);
            if ($normalized !== $release->getNormalizedVersion()) {
                Craft::warning("Ignoring package version {$this->package->name}:{$release->version} due to a version mismatch in composer.json: {$config['version']}", __METHOD__);
                $release->invalidate("version mismatch -- config says {$config['version']}; tag says {$release->version}");
                return false;
            }

            // Use the composer.json version value, in case it differs from the tag name
            $release->version = $config['version'];
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
