<?php

namespace craftcom\composer\vcs;

use craftcom\composer\PackageVersion;
use craftcom\errors\VcsException;

interface VcsInterface
{
    /**
     * @return string[]
     * @throws VcsException
     */
    public function getVersions(): array;

    /**
     * @param PackageVersion $version
     */
    public function populateVersion(PackageVersion $version);
}
