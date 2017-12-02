<?php

namespace craftcom\composer\vcs;

use craftcom\composer\PackageRelease;
use craftcom\errors\VcsException;

interface VcsInterface
{
    /**
     * @return string[]
     * @throws VcsException
     */
    public function getVersions(): array;

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
