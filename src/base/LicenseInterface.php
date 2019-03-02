<?php

namespace craftnet\base;

interface LicenseInterface
{
    /**
     * Returns the email address associated with the license.
     *
     * @return string
     */
    public function getEmail(): string;

    /**
     * Returns the license owner's ID.
     *
     * @return int|null
     */
    public function getOwnerId();

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
     * Returns whether the license is set to auto-renew.
     *
     * @return bool
     */
    public function getWillAutoRenew(): bool;

    /**
     * Returns the locked-in renewal price for the license.
     *
     * @return float
     */
    public function getRenewalPrice(): float;

    /**
     * Updates the locked-in renewal price for the license.
     *
     * @param float $renewalPrice
     */
    public function setRenewalPrice(float $renewalPrice);

    /**
     * Marks the license as having been reminded about an upcoming expiration/renewal date
     */
    public function markAsReminded();

    /**
     * Returns whether the license was reminded about the upcoming expiration/renewal date
     */
    public function getWasReminded(): bool;

    /**
     * Marks the license as expired
     */
    public function markAsExpired();

    /**
     * Returns the edition associated with the license.
     *
     * @return EditionInterface
     */
    public function getEdition(): EditionInterface;

    /**
     * Returns the license’s edit URL in Craft ID
     *
     * @return string
     */
    public function getEditUrl(): string;

    /**
     * Returns the license key.
     *
     * @return string
     */
    public function getKey(): string;

    /**
     * Returns a shortened version of the license key.
     *
     * @return string
     */
    public function getShortKey(): string;
}
