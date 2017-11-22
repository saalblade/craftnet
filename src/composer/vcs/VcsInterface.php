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
     * @param string $secret
     *
     * @throws VcsException
     */
    public function createWebhook(string $secret);
}
