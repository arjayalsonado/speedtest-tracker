<?php

namespace App\Livewire;

use App\Enums\ResultStatus;
use App\Models\Result;
use App\Support\SpeedtestLite\IspProfiles;
use App\Support\SpeedtestLite\UniqueEgressValidator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LatestResultStats extends Component
{
    #[Computed]
    public function latestResult(): ?Result
    {
        return Result::where('status', ResultStatus::Completed)
            ->latest()
            ->first();
    }

    #[Computed]
    public function latestResults(): Collection
    {
        $profiles = $this->configuredProfileOptions()
            ->union($this->storedProfileOptions())
            ->filter()
            ->unique();

        if ($profiles->isEmpty()) {
            return collect([
                [
                    'key' => null,
                    'name' => null,
                    'result' => $this->latestResult,
                    'uniqueEgressFailure' => false,
                ],
            ])->filter(fn (array $item): bool => $item['result'] instanceof Result);
        }

        return $profiles
            ->map(function (string $name, string $key): array {
                return [
                    'key' => $key,
                    'name' => $name,
                    'result' => $result = $this->latestDisplayResultForProfile($key),
                    'uniqueEgressFailure' => $result instanceof Result && UniqueEgressValidator::isFailure($result),
                ];
            })
            ->filter(fn (array $item): bool => $item['result'] instanceof Result)
            ->values();
    }

    public function render()
    {
        return view('livewire.latest-result-stats');
    }

    /**
     * @return Collection<string, string>
     */
    protected function configuredProfileOptions(): Collection
    {
        return IspProfiles::enabled()
            ->mapWithKeys(fn ($profile): array => [$profile->key => $profile->name]);
    }

    protected function latestDisplayResultForProfile(string $key): ?Result
    {
        return Result::query()
            ->where('isp_profile_key', $key)
            ->whereIn('status', [ResultStatus::Completed, ResultStatus::Failed])
            ->latest()
            ->limit(50)
            ->get()
            ->first(function (Result $result): bool {
                return $result->status === ResultStatus::Completed
                    || UniqueEgressValidator::isFailure($result);
            });
    }

    /**
     * @return Collection<string, string>
     */
    protected function storedProfileOptions(): Collection
    {
        return Result::query()
            ->select(['isp_profile_key', 'isp_profile_name'])
            ->whereNotNull('isp_profile_key')
            ->where('isp_profile_key', '!=', '')
            ->distinct()
            ->orderBy('isp_profile_key')
            ->get()
            ->mapWithKeys(function (Result $result): array {
                $key = (string) $result->isp_profile_key;
                $name = filled($result->isp_profile_name)
                    ? (string) $result->isp_profile_name
                    : strtoupper($key);

                return [$key => $name];
            });
    }
}
