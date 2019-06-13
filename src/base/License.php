<?php

namespace craftnet\base;

use Composer\Semver\Comparator;
use craft\base\Model;

/**
 * @property-read bool $hasGoneRogue
 */
abstract class License extends Model implements LicenseInterface
{
    /**
     * @inheritdoc
     */
    public function getHasGoneRogue(): bool
    {
        if (!$this->getIsExpirable()) {
            return false;
        }

        $lastVersion = $this->getLastVersion();
        $lastAllowedVersion = $this->getLastAllowedVersion();
        return $lastVersion && $lastAllowedVersion && Comparator::greaterThan($lastVersion, $lastAllowedVersion);
    }
}
