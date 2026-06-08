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

    public string $chartRange = '24h';

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

    #[Computed]
    public function chartRangeOptions(): array
    {
        return [
            '1h' => __('general.last_1h'),
            '6h' => __('general.last_6h'),
            '12h' => __('general.last_12h'),
            '24h' => __('general.last_24h'),
            'week' => __('general.last_week'),
            'month' => __('general.last_month'),
            'year' => __('general.last_year'),
        ];
    }

    public function mount(): void
    {
        $defaultRange = config('speedtest.default_chart_range', '24h');

        if (array_key_exists($defaultRange, $this->chartRangeOptions)) {
            $this->chartRange = $defaultRange;
        }
    }

    public function updatedIspProfileKey(string $value): void
    {
        if (! array_key_exists($value, $this->profileOptions)) {
            $this->ispProfileKey = 'all';
        }
    }

    public function updatedChartRange(string $value): void
    {
        if (! array_key_exists($value, $this->chartRangeOptions)) {
            $this->chartRange = '24h';
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
