<?php

use App\Actions\CheckForScheduledSpeedtests;
use App\Actions\VacuumDatabase;
use App\Support\SpeedtestLite\IspProfiles;
use Illuminate\Support\Facades\Schedule;

/**
 * Checks if Result model records should be pruned.
 */
Schedule::command('model:prune')
    ->daily()
    ->when(function () {
        return config('speedtest.prune_results_older_than') > 0;
    });

/**
 * Nightly maintenance
 */
Schedule::daily()
    ->group(function () {
        Schedule::command('queue:prune-batches --hours=48');
        Schedule::command('queue:prune-failed --hours=48');
    });

/**
 * Check for scheduled speedtests.
 *
 * Standard mode keeps the upstream every-minute scheduler check.
 * Multi-ISP mode schedules one command per enabled ISP profile.
 */
if (config('speedtest-lite.mode') === 'multi_isp_experimental') {
    foreach (IspProfiles::enabled() as $profile) {
        if (! $profile->cron) {
            continue;
        }

        Schedule::command("speedtest-lite:run-isp-profile {$profile->key}")
            ->cron($profile->cron)
            ->name("mikrotik-lite-speedtest-{$profile->key}")
            ->withoutOverlapping(20);
    }
} else {
    Schedule::everyMinute()
        ->group(function () {
            Schedule::call(fn () => CheckForScheduledSpeedtests::run());
        });
}

/**
 * Weekly SQLite maintenance (no-op on other drivers).
 */
Schedule::call(fn () => VacuumDatabase::run())
    ->weekly()
    ->sundays()
    ->at('03:15')
    ->name('sqlite-vacuum')
    ->onOneServer();
