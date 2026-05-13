<?php

namespace App\Console\Commands;

use App\Actions\CheckForScheduledSpeedtests;
use App\Models\Result;
use App\Support\SpeedtestLite\IspProfiles;
use Illuminate\Console\Command;
use Throwable;

class RunIspProfileSpeedtest extends Command
{
    protected $signature = 'speedtest-lite:run-isp-profile {profile : ISP profile key, for example isp1}';

    protected $description = 'Run a scheduled Speedtest Tracker check bound to a configured ISP source IP.';

    public function handle(): int
    {
        $profile = IspProfiles::get((string) $this->argument('profile'));

        $this->info("Running speed test for {$profile->name} using source IP {$profile->sourceIp}...");

        $beforeId = Result::query()->max('id') ?? 0;

        config([
            'speedtest.interface' => $profile->sourceIp,
            'speedtest.schedule' => '* * * * *',
            'speedtest-lite.active_profile_key' => $profile->key,
            'speedtest-lite.active_profile_name' => $profile->name,
            'speedtest-lite.active_source_ip' => $profile->sourceIp,
        ]);

        putenv('SPEEDTEST_INTERFACE='.$profile->sourceIp);
        $_ENV['SPEEDTEST_INTERFACE'] = $profile->sourceIp;
        $_SERVER['SPEEDTEST_INTERFACE'] = $profile->sourceIp;

        try {
            CheckForScheduledSpeedtests::run();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            report($exception);

            return self::FAILURE;
        }

        $result = Result::query()
            ->where('id', '>', $beforeId)
            ->latest('id')
            ->first();

        if (! $result) {
            $this->warn('No new result was detected after running the profile. Check queue mode and upstream action behavior.');

            return self::SUCCESS;
        }

        $result->forceFill([
            'isp_profile_key' => $profile->key,
            'isp_profile_name' => $profile->name,
            'source_ip' => $profile->sourceIp,
        ])->save();

        $this->info("Tagged result #{$result->getKey()} as {$profile->name} ({$profile->sourceIp}).");

        return self::SUCCESS;
    }
}
