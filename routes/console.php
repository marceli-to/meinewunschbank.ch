<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
	$this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Shared hosting has no supervisor, so cron drains the mail queue once a
// minute instead of a long-running worker.
Schedule::command('queue:work --stop-when-empty --max-time=50')
	->everyMinute()
	->withoutOverlapping();
