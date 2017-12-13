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
                    continue;
                }

                $versions[$tag['name']] = $tag['commit']['sha'];
            }
        } while (count($tags) === 100);

        return $versions;
    }

    /**
     * @inheritdoc
     */
    public function getChangelogUrl()
    {
        if (($plugin = $this->package->getPlugin()) && $plugin->changelogPath) {
            return rtrim($this->package->repository, '/').'/blob/HEAD/'.$plugin->changelogPath;
        }
        return null;
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
            Craft::$app->getErrorHandler()->logException($e);
            $release->invalidate("error loading composer.json: {$e->getMessage()}");
            return;
        }

        if ($this->populateReleaseFromComposerConfig($release, $config) === false) {
            return;
        }

        if ($release->time === null) {
            try {
                $commit = $api->commits()->show($this->owner, $this->repo, $release->sha);
                $release->time = $commit['commit']['committer']['date'];
            } catch (RuntimeException $e) {
                Craft::warning("Couldn't determine the release time for {$this->package->name}:{$release->version} due to error loading commit info: {$e->getMessage()}", __METHOD__);
                Craft::$app->getErrorHandler()->logException($e);
            }
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
                Craft::$app->getErrorHandler()->logException($e);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function createWebhook()
    {
        /** @var Repo $api */
        $api = $this->client->api('repo');

        $params = [
            'name' => 'web',
            'events' => ['create', 'push', 'release', 'delete'],
            'active' => true,
            'config' => [
                'url' => 'https://api.craftcms.com/webhook/github',
                'content_type' => 'json',
                'secret' => $this->package->webhookSecret,
            ],
        ];

        try {
            $info = $api->hooks()->create($this->owner, $this->repo, $params);
        } catch (RuntimeException $e) {
            throw new VcsException($e->getMessage(), $e->getCode(), $e);
        }

        $this->package->webhookId = $info['id'];
    }

    /**
     * @inheritdoc
     */
    public function deleteWebhook()
    {
        /** @var Repo $api */
        $api = $this->client->api('repo');

        try {
            $api->hooks()->remove($this->owner, $this->repo, $this->package->webhookId);
        } catch (RuntimeException $e) {
            throw new VcsException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
