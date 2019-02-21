<?php

namespace craftnet\helpers;

class LicenseHelper
{
    /**
     * Get expiry date options.
     *
     * @param \DateTime $expiryDate
     * @return array
     * @throws \Exception
     */
    public static function getExpiryDateOptions(\DateTime $expiryDate): array
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $dates = [];

        for ($i = 1; $i <= 5; $i++) {
            if ($expiryDate < $now) {
                $date =  (new \DateTime('now', new \DateTimeZone('UTC')))
                    ->modify("+{$i} years");
                $dates[] = ["{$i}y", $date->format('Y-m-d')];
            } else {
                $date = clone $expiryDate;
                $date =  $date->modify("+{$i} years");
                $dates[] = ["{$date->format('Y-m-d')}", $date->format('Y-m-d')];
            }
        }

        return $dates;
    }
}
