<?php

namespace App\Support\SpeedtestLite;

use App\Enums\ResultStatus;
use App\Models\Result;
use Illuminate\Support\Arr;

final class UniqueEgressValidator
{
    /**
     * @return array<string, mixed>|null
     */
    public function findCollision(Result $result): ?array
    {
        if (! $this->enabled()) {
            return null;
        }

        if ($result->status !== ResultStatus::Completed) {
            return null;
        }

        $currentProfileKey = trim((string) $result->isp_profile_key);
        $externalIp = trim((string) $result->ip_address);

        if ($currentProfileKey === '' || $externalIp === '') {
            return null;
        }

        $enabledProfiles = IspProfiles::enabled();

        if (! $enabledProfiles->has($currentProfileKey) || $enabledProfiles->count() < 2) {
            return null;
        }

        $otherProfileKeys = $enabledProfiles
            ->keys()
            ->reject(fn (string $key): bool => $key === $currentProfileKey)
            ->values();

        $matchedResult = Result::query()
            ->where('id', '!=', $result->getKey())
            ->where('status', ResultStatus::Completed)
            ->whereIn('isp_profile_key', $otherProfileKeys)
            ->where('created_at', '>=', now()->subMinutes($this->windowMinutes()))
            ->latest()
            ->limit(100)
            ->get()
            ->first(fn (Result $candidate): bool => trim((string) $candidate->ip_address) === $externalIp);

        if (! $matchedResult instanceof Result) {
            return null;
        }

        return [
            'reason' => 'external_ip_collision',
            'message' => 'Downtime/failover detected: this profile exited through the same external IP as another enabled profile.',
            'external_ip' => $externalIp,
            'current_profile_key' => $currentProfileKey,
            'current_profile_name' => $result->isp_profile_name,
            'matched_profile_key' => $matchedResult->isp_profile_key,
            'matched_profile_name' => $matchedResult->isp_profile_name,
            'matched_result_id' => $matchedResult->getKey(),
            'matched_created_at' => optional($matchedResult->created_at)->toIso8601String(),
            'window_minutes' => $this->windowMinutes(),
        ];
    }

    /**
     * @param  array<string, mixed>  $collision
     */
    public function markFailed(Result $result, array $collision): Result
    {
        $data = $result->data ?? [];

        Arr::set($data, 'speedtest_lite.unique_egress', [
            'status' => 'failed',
            ...$collision,
        ]);

        $data['message'] = (string) $collision['message'];

        $result->forceFill([
            'status' => ResultStatus::Failed,
            'healthy' => false,
            'data' => $data,
        ])->save();

        return $result->refresh();
    }

    public static function isFailure(Result $result): bool
    {
        return $result->status === ResultStatus::Failed
            && Arr::get($result->data, 'speedtest_lite.unique_egress.status') === 'failed';
    }

    private function enabled(): bool
    {
        return (bool) config('speedtest-lite.unique_egress_required', false);
    }

    private function windowMinutes(): int
    {
        return max(1, (int) config('speedtest-lite.unique_egress_window_minutes', 60));
    }
}
