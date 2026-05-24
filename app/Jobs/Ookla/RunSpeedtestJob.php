<?php

namespace App\Jobs\Ookla;

use App\Enums\ResultStatus;
use App\Events\SpeedtestFailed;
use App\Events\SpeedtestRunning;
use App\Helpers\Ookla;
use App\Models\Result;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\SkipIfBatchCancelled;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class RunSpeedtestJob implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Result $result,
    ) {}

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            new SkipIfBatchCancelled,
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->result->update([
            'status' => ResultStatus::Running,
        ]);

        SpeedtestRunning::dispatch($this->result);

        $bindValue = config('speedtest.interface');
        $bindOption = config('speedtest-lite.bind_option', '--interface');

        if (! in_array($bindOption, ['--interface', '--ip'], true)) {
            $bindOption = '--interface';
        }

        $bindArgument = $bindValue ? "{$bindOption}={$bindValue}" : null;

        Log::info('Running Ookla speedtest command.', [
            'result_id' => $this->result->getKey(),
            'bind_option' => $bindOption,
            'bind_value' => $bindValue,
            'bind_argument' => $bindArgument,
        ]);

        $command = array_filter([
            'speedtest',
            '--accept-license',
            '--accept-gdpr',
            '--selection-details',
            '--format=json',
            $this->result->server_id ? '--server-id='.$this->result->server_id : null,
            $bindArgument,
        ]);

        $process = new Process($command);
        $process->setTimeout(300);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            $this->result->update([
                'data->type' => 'log',
                'data->level' => 'error',
                'data->message' => Ookla::getErrorMessage($exception),
                'status' => ResultStatus::Failed,
            ]);

            $this->batch()->cancel();

            SpeedtestFailed::dispatch($this->result);

            return;
        }

        $output = json_decode($process->getOutput(), true);

        $this->result->update([
            'ping' => Arr::get($output, 'ping.latency'),
            'download' => Arr::get($output, 'download.bandwidth'),
            'upload' => Arr::get($output, 'upload.bandwidth'),
            'download_bytes' => Arr::get($output, 'download.bytes'),
            'upload_bytes' => Arr::get($output, 'upload.bytes'),
            'data' => $output,
        ]);
    }
}
