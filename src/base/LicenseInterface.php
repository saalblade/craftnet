<?php

namespace craftnet\base;

interface LicenseInterface
{
    /**
     * Returns whether the license is expirable.
     *
     * @return bool
     */
    public function getIsExpirable(): bool;

    /**
     * Returns the license's expiry date.
     *
     * @return \DateTime|null
     */
    public function getExpiryDate();

    /**
     * Returns the edition associated with the license.
     *
     * @return EditionInterface
     */
    public function getEdition(): EditionInterface;
}
