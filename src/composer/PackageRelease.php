<?php

namespace craftcom\composer;

use Composer\Semver\VersionParser;
use craft\base\Model;
use craft\helpers\Json;

/**
 * @property mixed $normalizedVersion
 * @property mixed $stability
 */
class PackageRelease extends Model
{
    /**
     * @var array
     */
    private static $_releaseJsonColumns = [
        'keywords',
        'license',
        'authors',
        'support',
        'conflict',
        'replace',
        'provide',
        'suggest',
        'autoload',
        'includePaths',
        'extra',
        'binaries',
        'source',
        'dist',
    ];

    /**
     * @var
     */
    public $id;

    /**
     * @var
     */
    public $packageId;

    /**
     * @var
     */
    public $sha;

    /**
     * @var
     */
    public $description;

    /**
     * @var
     */
    public $version;

    /**
     * @var string
     */
    public $type = 'library';

    /**
     * @var
     */
    public $keywords;

    /**
     * @var
     */
    public $homepage;

    /**
     * @var
     */
    public $time;

    /**
     * @var
     */
    public $license;

    /**
     * @var
     */
    public $authors;

    /**
     * @var
     */
    public $support;

    /**
     * @var
     */
    public $require;

    /**
     * @var
     */
    public $conflict;

    /**
     * @var
     */
    public $replace;

    /**
     * @var
     */
    public $provide;

    /**
     * @var
     */
    public $suggest;

    /**
     * @var
     */
    public $autoload;

    /**
     * @var
     */
    public $includePaths;

    /**
     * @var
     */
    public $targetDir;

    /**
     * @var
     */
    public $extra;

    /**
     * @var
     */
    public $binaries;

    /**
     * @var
     */
    public $source;

    /**
     * @var
     */
    public $dist;

    /**
     * @var
     */
    public $changelog;

    /**
     * @var bool
     */
    public $valid = true;

    /**
     *
     */
    public function __construct(array $config = [])
    {
        foreach (self::$_releaseJsonColumns as $column) {
            if (isset($config[$column]) && is_string($config[$column])) {
                $config[$column] = Json::decode($config[$column]);
            }
        }

        parent::__construct($config);
    }

    /**
     *
     */
    public function init()
    {
        if (is_string($this->source)) {
            $this->source = Json::decode($this->source);
        }
        if (is_string($this->dist)) {
            $this->dist = Json::decode($this->dist);
        }
    }

    /**
     * Invalidates the version so it won't be available to Composer.
     */
    public function invalidate()
    {
        $this->valid = false;

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

    /**
     * @return string
     */
    public function getNormalizedVersion(): string
    {
        return (new VersionParser())->normalize($this->version);
    }

    /**
     * @return string
     */
    public function getStability(): string
    {
        return VersionParser::parseStability($this->version);
    }
}
