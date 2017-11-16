<?php

namespace craftcom\composer\vcs;

use Composer\Semver\Comparator;
use Craft;
use craft\helpers\Json;
use craftcom\composer\PackageVersion;
use craftcom\errors\VcsException;
use Github\Api\Repo;
use Github\Client;
use Github\Exception\RuntimeException;

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

    private $_versionDates;

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

    public function populateVersion(PackageVersion $version)
    {
        // Get the composer.json contents
        /** @var Repo $api */
        $api = $this->client->api('repo');
        try {
            $response = $api->contents()->show($this->owner, $this->repo, 'composer.json', $version->sha);
            $config = Json::decode(base64_decode($response['content']));
        } catch (RuntimeException $e) {
            Craft::warning("Ignoring package version {$this->package->name}:{$version->version} due to error loading composer.json: {$e->getMessage()}", __METHOD__);
            $version->nullify();
            return;
        }

        if ($this->populateVersionFromComposerConfig($version, $config) === false) {
            return;
        }

        if ($version->time === null) {
            $version->time = $this->_versionDates[$version->version];
        }

        $version->dist = [
            'type' => 'zip',
            'url' => "https://api.github.com/repos/{$this->owner}/{$this->repo}/zipball/{$version->sha}",
            'reference' => $version->sha,
            'shasum' => '',
        ];
    }
}
