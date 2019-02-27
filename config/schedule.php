<?php

/** @var $schedule omnilight\scheduling\Schedule */

$schedule->command('craftnet/packages/update-deps --queue')
    ->daily()
    ->withoutOverlapping();
