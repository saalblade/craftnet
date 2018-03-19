<?php

namespace craftnet\composer\vcs;

use craftnet\composer\PackageRelease;
use craftnet\errors\VcsException;

interface VcsInterface
{
    /**
     * @return string[]
     * @throws VcsException
     */
    public function getVersions(): array;

    /**
     * @return string|null
     */
    public function getChangelogUrl();

    /**
     * @param PackageRelease $release
     */
    public function populateRelease(PackageRelease $release);

    /**
     * @throws VcsException
     */
    public function createWebhook();

    /**
     * @throws VcsException
     */
    public function deleteWebhook();
}
