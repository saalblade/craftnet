<?php

namespace craftcom\composer;

use Craft;
use craft\base\Model;
use craftcom\composer\vcs\BaseVcs;
use craftcom\composer\vcs\GitHub;
use craftcom\composer\vcs\Packagist;
use craftcom\composer\vcs\VcsInterface;
use craftcom\errors\MissingTokenException;
use craftcom\Module;
use craftcom\plugins\Plugin;
use craftcom\services\Oauth;
use Github\Client;

/**
 * @property Plugin|null  $plugin
 * @property VcsInterface $vcs
 */
class Package extends Model
{
    /**
     * @var
     */
    public $id;

    /**
     * @var
     */
    public $name;

    /**
     * @var
     */
    public $type;

    /**
     * @var
     */
    public $repository;

    /**
     * @var bool
     */
    public $managed = false;

    /**
     * @var bool
     */
    public $abandoned = false;

    /**
     * @var
     */
    public $replacementPackage;

    /**
     * @var
     */
    public $webhookId;

    /**
     * @var
     */
    public $webhookSecret;

    /**
     * @var BaseVcs|null
     */
    private $_vcs;

    /**
     * @var Plugin|false|null
     */
    private $_plugin;

    /**
     * @return VcsInterface
     * @throws MissingTokenException
     */
    public function getVcs(): VcsInterface
    {
        return $this->_vcs ?? $this->_vcs = $this->_createVcs();
    }

    /**
     * @param string|false $value The replacement package name, or false
     */
    public function setAbandoned($value)
    {
        if ((bool)$this->abandoned !== ($abandoned = (bool)$value)) {
            $this->abandoned = $abandoned;
            $this->replacementPackage = ($abandoned && is_string($value)) ? $value : null;
            Module::getInstance()->getPackageManager()->savePackage($this);
        }
    }

    /**
     * Returns the plugin associated with this package, if any.
     *
     * @return Plugin|null
     */
    public function getPlugin()
    {
        if ($this->type !== 'craft-plugin') {
            return null;
        }

        if ($this->_plugin === null) {
            $this->_plugin = Plugin::find()
                ->packageId($this->id)
                ->status(null)
                ->one()
                ?? false;
        }

        return $this->_plugin ?: null;
    }

    /**
     * @return VcsInterface
     * @throws MissingTokenException
     */
    private function _createVcs(): VcsInterface
    {
        $parsed = $this->repository ? parse_url($this->repository) : null;

        if (isset($parsed['host']) && $parsed['host'] === 'github.com') {
            list($owner, $repo) = explode('/', trim($parsed['path'], '/'), 2);

            // Create an authenticated GitHub API client
            $client = new Client();

            $token = null;
            if ($plugin = $this->getPlugin()) {
                $token = Module::getInstance()->getOauth()->getAuthTokenByUserId('Github', $plugin->developerId);
                Craft::info('Using package token for '.$plugin->name.' : '.$token, __METHOD__);

                if (!$token) {
                    if (Module::getInstance()->getPackageManager()->requirePluginVcsTokens) {
                        throw new MissingTokenException($plugin);
                    }
                    Craft::warning("Plugin \"{$plugin->name}\" is missing its VCS token.", __METHOD__);
                }
            }
            if (!$token) {
                // Just use a fallback token
                $token = Module::getInstance()->getPackageManager()->getRandomGitHubFallbackToken();
                Craft::info('Using fallback token for '.($plugin ? $plugin->name : $this->name).' : '.$token, __METHOD__);
            }

            if ($token) {
                $client->authenticate($token, null, Client::AUTH_HTTP_TOKEN);
            }

            return new GitHub($this, [
                'client' => $client,
                'owner' => $owner,
                'repo' => $repo
            ]);
        }

        return new Packagist($this);
    }
}
