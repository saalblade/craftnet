<?php

namespace craftcom\composer;

use Composer\Semver\VersionParser;
use craft\base\Model;
use craft\helpers\Json;

class PackageRelease extends Model
{
    public $id;
    public $packageId;
    public $sha;
    public $description;
    public $version;
    public $type = 'library';
    public $keywords;
    public $homepage;
    public $time;
    public $license;
    public $authors;
    public $support;
    public $require;
    public $conflict;
    public $replace;
    public $provide;
    public $suggest;
    public $autoload;
    public $includePaths;
    public $targetDir;
    public $extra;
    public $binaries;
    public $source;
    public $dist;
    public $changelog;

    public function init()
    {
        if (is_string($this->source)) {
            $this->source = Json::decode($this->source);
        }
        if (is_string($this->dist)) {
            $this->dist = Json::decode($this->dist);
        }
    }

    public function nullify()
    {
        $this->description = null;
        $this->type = null;
        $this->keywords = null;
        $this->homepage = null;
        $this->time = null;
        $this->license = null;
        $this->authors = null;
        $this->support = null;
        $this->require = null;
        $this->conflict = null;
        $this->replace = null;
        $this->provide = null;
        $this->suggest = null;
        $this->autoload = null;
        $this->includePaths = null;
        $this->targetDir = null;
        $this->extra = null;
        $this->binaries = null;
        $this->source = null;
        $this->dist = null;
        $this->changelog = null;
    }

    public function getNormalizedVersion()
    {
        return (new VersionParser())->normalize($this->version);
    }

    public function getStability()
    {
        return VersionParser::parseStability($this->version);
    }
}
