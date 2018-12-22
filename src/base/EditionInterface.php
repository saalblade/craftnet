<?php

namespace craftnet\base;

interface EditionInterface extends PurchasableInterface
{
    /**
     * Returns the edition handle.
     *
     * @return string
     */
    public function getHandle(): string;

    /**
     * Returns the renewal purchasable associated with this edition.
     *
     * @return RenewalInterface
     */
    public function getRenewal(): RenewalInterface;
}
