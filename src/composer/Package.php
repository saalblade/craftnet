<?php

namespace craftcom\composer;

use craft\base\Model;
use craftcom\composer\vcs\GitHub;
use craftcom\composer\vcs\Packagist;
use craftcom\composer\vcs\VcsInterface;
use craftcom\Module;

/**
 * @property VcsInterface $vcs
 */
class Package extends Model
{
    public $id;
    public $name;
    public $type;
    public $repository;
    public $managed = false;
    public $latestVersion;
    public $abandoned = false;
    public $replacementPackage;
    private $_vcs;

    public function getVcs(): VcsInterface
    {
        return $this->_vcs ?? $this->_vcs = $this->_createVcs();
    }

    public function setAbandoned($value)
    {
        if ((bool)$this->abandoned !== ($abandoned = (bool)$value)) {
            $this->abandoned = $abandoned;
            $this->replacementPackage = ($abandoned && is_string($value)) ? $value : null;
            Module::getInstance()->getPackageManager()->savePackage($this);
        }
    }

    private function _createVcs(): VcsInterface
    {
        $parsed = $this->repository ? parse_url($this->repository) : null;

        if (isset($parsed['host']) && $parsed['host'] === 'github.com') {
            list($owner, $repo) = explode('/', trim($parsed['path'], '/'), 2);
            return new GitHub($this, [
                'owner' => $owner,
                'repo' => $repo
            ]);
        }

        return new Packagist($this);
    }
}
