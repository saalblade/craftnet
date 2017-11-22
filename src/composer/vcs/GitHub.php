<?php

namespace craftcom\composer\vcs;

use Composer\Semver\Comparator;
use Craft;
use craft\helpers\Json;
use craftcom\composer\PackageRelease;
use craftcom\errors\VcsException;
use Github\Api\Repo;
use Github\Client;
use Github\Exception\RuntimeException;

/**
 * @property array $versions
 */
class GitHub extends BaseVcs
{
    /**
     * @var Client The GitHub API client to use
     */
    public $client;

    /**
     * @var string
     */
    public $owner;

    /**
     * @var string
     */
    public $repo;

    /**
     * @var
     */
    private $_versionDates;

    /**
     * @return array
     * @throws VcsException
     */
    public function getVersions(): array
    {
        $versions = [];
        /** @var Repo $api */
        $api = $this->client->api('repo');
        $page = 1;

        do {
            try {
                $tags = $api->tags($this->owner, $this->repo, [
                    'per_page' => 100,
                    'page' => $page++
                ]);
            } catch (RuntimeException $e) {
                throw new VcsException($e->getMessage(), $e->getCode(), $e);
            }

            foreach ($tags as $tag) {
                // Special case for Craft CMS - avoid any versions before the 3.0 Beta
                if ($this->package->name === 'craftcms/cms' && Comparator::lessThan($tag['name'], '3.0.0-beta.1')) {
                    break;
                }

                $versions[$tag['name']] = $tag['commit']['sha'];

                // Store the date for later, if needed
                if (isset($tag['tagger']['date'])) {
                    $this->_versionDates[$tag['name']] = $tag['tagger']['date'];
                }
            }
        } while (count($tags) === 100);

        return $versions;
    }

    /**
     * @param PackageRelease $release
     */
    public function populateRelease(PackageRelease $release)
    {
        // Get the composer.json contents
        /** @var Repo $api */
        $api = $this->client->api('repo');
        try {
            $response = $api->contents()->show($this->owner, $this->repo, 'composer.json', $release->sha);
            $config = Json::decode(base64_decode($response['content']));
        } catch (RuntimeException $e) {
            Craft::warning("Ignoring package version {$this->package->name}:{$release->version} due to error loading composer.json: {$e->getMessage()}", __METHOD__);
            $release->nullify();
            return;
        }

        if ($this->populateReleaseFromComposerConfig($release, $config) === false) {
            return;
        }

        if ($release->time === null) {
            $release->time = $this->_versionDates[$release->version];
        }

        $release->dist = [
            'type' => 'zip',
            'url' => "https://api.github.com/repos/{$this->owner}/{$this->repo}/zipball/{$release->sha}",
            'reference' => $release->sha,
            'shasum' => '',
        ];

        // Get the changelog contents if this is Craft or a plugin
        if ($this->package->name === 'craftcms/cms') {
            $changelogPath = 'CHANGELOG-v3.md';
        } else if ($plugin = $this->package->getPlugin()) {
            $changelogPath = $plugin->changelogPath;
        } else {
            $changelogPath = null;
        }

        if ($changelogPath) {
            try {
                $response = $api->contents()->show($this->owner, $this->repo, $changelogPath, $release->sha);
                $release->changelog = base64_decode($response['content']);
            } catch (RuntimeException $e) {
                Craft::warning("Couldn't fetch changelog for {$this->package->name}:{$release->version} due to error loading {$changelogPath}: {$e->getMessage()}", __METHOD__);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function createWebhook(string $secret)
    {
        /** @var Repo $api */
        $api = $this->client->api('repo');

        $params = [
            'name' => 'web',
            'events' => ['push'],
            'active' => true,
            'config' => [
                'url' => 'https://api.craftcms.com/github/push',
                'content_type' => 'json',
                'secret' => $secret,
            ],
        ];

        try {
            $api->hooks()->create($this->owner, $this->repo, $params);
        } catch (RuntimeException $e) {
            throw new VcsException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
