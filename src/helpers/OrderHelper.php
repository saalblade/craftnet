<?php

namespace craftnet\helpers;

abstract class OrderHelper
{
    /**
     * Calculates the difference in years between two dates.
     *
     * @param \DateTime $d1
     * @param \DateTime $d2
     * @return float
     */
    public static function dateDiffInYears(\DateTime $d1, \DateTime $d2): float
    {
        if ($d1->getTimestamp() === $d2->getTimestamp()) {
            return 0;
        }

        // Make sure $d2 is greater than $d1
        if ($d1 > $d2) {
            list($d1, $d2) = [$d2, $d1];
        }

        $d1 = new \DateTime($d1->format('Y-m-d'), new \DateTimeZone('UTC'));
        $d2 = new \DateTime($d2->format('Y-m-d'), new \DateTimeZone('UTC'));
        $diff = $d2->diff($d1);
        $years = $diff->y;

        // Calculate the difference in % into the year each date is
        if ($diff->m !== 0 || $diff->d !== 0) {
            $d1Pct = $d1->format('z') / ($d1->format('L') ? 365 : 364);
            $d2Pct = $d2->format('z') / ($d2->format('L') ? 365 : 364);
            if ($d2Pct >= $d1Pct) {
                $years += $d2Pct - $d1Pct;
            } else {
                $years += 1 - ($d1Pct - $d2Pct);
            }
        }

        return $years;
    }
}
