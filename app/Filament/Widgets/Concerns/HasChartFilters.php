<?php

namespace App\Filament\Widgets\Concerns;

use App\Enums\ResultStatus;
use App\Models\Result;
use App\Support\SpeedtestLite\UniqueEgressValidator;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait HasChartFilters
{
    public ?string $ispProfileKey = null;

    public bool $showChartRangeFilter = true;

    protected function getFilters(): ?array
    {
        if (! $this->showChartRangeFilter) {
            return null;
        }

        return $this->chartRangeOptions();
    }

    protected function chartRangeOptions(): array
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

    protected function applyChartRangeFilter(Builder $query): void
    {
        match ($this->filter) {
            '1h' => $query->where('created_at', '>=', now()->subHour()),
            '6h' => $query->where('created_at', '>=', now()->subHours(6)),
            '12h' => $query->where('created_at', '>=', now()->subHours(12)),
            '24h' => $query->where('created_at', '>=', now()->subDay()),
            'week' => $query->where('created_at', '>=', now()->subWeek()),
            'month' => $query->where('created_at', '>=', now()->subMonth()),
            'year' => $query->where('created_at', '>=', now()->subYear()),
            default => null,
        };
    }

    protected function applyIspProfileFilter(Builder $query): void
    {
        if (blank($this->ispProfileKey) || $this->ispProfileKey === 'all') {
            return;
        }

        $query->where('isp_profile_key', $this->ispProfileKey);
    }

    protected function applyDashboardChartFilters(Builder $query): void
    {
        $this->applyChartRangeFilter($query);
        $this->applyIspProfileFilter($query);
    }

    /**
     * @param  array<int, string>  $select
     * @return Collection<int, Result>
     */
    protected function dashboardChartResults(array $select): Collection
    {
        $select = collect($select)
            ->merge(['id', 'status', 'data', 'created_at', 'isp_profile_key', 'isp_profile_name'])
            ->unique()
            ->values()
            ->all();

        $query = Result::query()
            ->select($select)
            ->whereIn('status', [ResultStatus::Completed, ResultStatus::Failed]);

        $this->applyDashboardChartFilters($query);

        return $query
            ->orderBy('created_at')
            ->get()
            ->filter(fn (Result $result): bool => $result->status === ResultStatus::Completed || UniqueEgressValidator::isFailure($result))
            ->values();
    }

    /**
     * @param  Collection<int, Result>  $results
     * @return Collection<int, Result>
     */
    protected function completedChartResults(Collection $results): Collection
    {
        return $results
            ->filter(fn (Result $result): bool => $result->status === ResultStatus::Completed)
            ->values();
    }

    /**
     * @param  Collection<int, Result>  $results
     * @return Collection<int, string>
     */
    protected function chartLabels(Collection $results): Collection
    {
        return $results->map(fn (Result $result): string => $result->created_at
            ->timezone(config('app.display_timezone'))
            ->format(config('app.chart_datetime_format')));
    }

    /**
     * @param  Collection<int, Result>  $results
     * @return array<string, mixed>
     */
    protected function notMeasuredDataset(Collection $results): array
    {
        return [
            'type' => 'bar',
            'label' => __('general.not_measured'),
            'data' => $results->map(fn (Result $result): ?int => UniqueEgressValidator::isFailure($result) ? 1 : null),
            'backgroundColor' => 'rgba(245, 158, 11, 0.85)',
            'borderColor' => 'rgba(245, 158, 11, 1)',
            'borderWidth' => 1,
            'barThickness' => 4,
            'borderSkipped' => false,
            'yAxisID' => 'notMeasured',
            'order' => 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function notMeasuredScaleOptions(): array
    {
        return [
            'display' => false,
            'position' => 'right',
            'min' => 0,
            'max' => 1,
            'grid' => [
                'drawOnChartArea' => false,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function sharedTooltipOptions(): array
    {
        return [
            'enabled' => true,
            'mode' => 'index',
            'intersect' => false,
            'position' => 'nearest',
            'callbacks' => [
                'label' => RawJs::make(<<<'JS'
                    function (context) {
                        if (context.dataset.label === 'Not measured') {
                            return 'Not measured - failover/egress collision';
                        }

                        const label = context.dataset.label || '';
                        const value = context.formattedValue;

                        return label ? `${label}: ${value}` : value;
                    }
                JS),
            ],
        ];
    }
}
