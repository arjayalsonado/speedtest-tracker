<?php

namespace App\Livewire;

use App\Models\Result;
use App\Support\SpeedtestLite\IspProfiles;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DashboardMetrics extends Component
{
    public string $ispProfileKey = 'all';

    #[Computed]
    public function profileOptions(): array
    {
        $profiles = $this->configuredProfileOptions()
            ->union($this->storedProfileOptions())
            ->filter()
            ->unique();

        return collect(['all' => __('general.all_profiles')])
            ->merge($profiles)
            ->all();
    }

    public function updatedIspProfileKey(string $value): void
    {
        if (! array_key_exists($value, $this->profileOptions)) {
            $this->ispProfileKey = 'all';
        }
    }

    public function render()
    {
        return view('livewire.dashboard-metrics');
    }

    /**
     * @return Collection<string, string>
     */
    protected function configuredProfileOptions(): Collection
    {
        return IspProfiles::enabled()
            ->mapWithKeys(fn ($profile): array => [$profile->key => $profile->name]);
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
