<?php

namespace App\Console\Commands;

use App\Support\SpeedtestLite\IspProfiles;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ValidateIspProfiles extends Command
{
    protected $signature = 'speedtest-lite:validate-isp-profiles';

    protected $description = 'Validate configured MikroTik Lite ISP profiles and source IP visibility.';

    public function handle(): int
    {
        $interface = config('speedtest-lite.bind_interface', 'eth0');
        $profiles = IspProfiles::enabled();

        if ($profiles->isEmpty()) {
            $this->warn('No enabled ISP profiles found.');

            return self::SUCCESS;
        }

        $process = new Process(['ip', '-o', 'addr', 'show', 'dev', $interface]);
        $process->run();

        $ipOutput = $process->isSuccessful() ? $process->getOutput() : '';

        foreach ($profiles as $profile) {
            $visible = str_contains($ipOutput, $profile->sourceIp);

            $this->line(sprintf(
                '[%s] %s source_ip=%s visible=%s cron=%s',
                $profile->key,
                $profile->name,
                $profile->sourceIp,
                $visible ? 'yes' : 'no',
                $profile->cron ?: 'not-set',
            ));

            if (! $visible) {
                $this->warn("Source IP [{$profile->sourceIp}] is not visible on [{$interface}]. Check RouterOS VETH address list.");
            }
        }

        return self::SUCCESS;
    }
}
