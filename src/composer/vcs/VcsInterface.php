<?php

namespace craftcom\composer\vcs;

use craftcom\composer\PackageVersion;

interface VcsInterface
{
    /**
     * @return string[]
     */
    public function getVersions(): array;

    /**
     * @param PackageVersion $version
     */
    public function populateVersion(PackageVersion $version);
}
