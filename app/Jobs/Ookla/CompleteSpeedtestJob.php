<?php

namespace App\Jobs\Ookla;

use App\Enums\ResultStatus;
use App\Events\SpeedtestCompleted;
use App\Events\SpeedtestFailed;
use App\Models\Result;
use App\Support\SpeedtestLite\UniqueEgressValidator;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\SkipIfBatchCancelled;

class CompleteSpeedtestJob implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Result $result,
    ) {}

    /**
     * Get the middleware the job should pass through.
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
        $attributes = [
            'status' => ResultStatus::Completed,
        ];

        // Set by RunIspProfileSpeedtest before CheckForScheduledSpeedtests dispatches the job chain.
        if (filled(config('speedtest-lite.active_profile_key'))) {
            $attributes['isp_profile_key'] = config('speedtest-lite.active_profile_key');
            $attributes['isp_profile_name'] = config('speedtest-lite.active_profile_name');
            $attributes['source_ip'] = config('speedtest-lite.active_source_ip');
        }

        $this->result->forceFill($attributes)->save();

        $validator = app(UniqueEgressValidator::class);
        $collision = $validator->findCollision($this->result->refresh());

        if ($collision !== null) {
            SpeedtestFailed::dispatch($validator->markFailed($this->result, $collision));

            return;
        }

        SpeedtestCompleted::dispatch($this->result->refresh());
    }
}
