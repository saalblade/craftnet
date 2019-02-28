<?php

/** @var $schedule omnilight\scheduling\Schedule */

$schedule->command('craftnet/licenses/send-reminders')
    ->daily()
    ->withoutOverlapping();

$schedule->command('craftnet/packages/update-deps --queue')
    ->daily()
    ->withoutOverlapping();
