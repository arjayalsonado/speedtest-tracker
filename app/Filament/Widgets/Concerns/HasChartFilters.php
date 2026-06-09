<?php

namespace App\Filament\Widgets\Concerns;

use App\Enums\ResultStatus;
use App\Models\Result;
use App\Support\SpeedtestLite\UniqueEgressValidator;
use Closure;
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

    protected function isAllProfilesChart(): bool
    {
        return blank($this->ispProfileKey) || $this->ispProfileKey === 'all';
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
    protected function notMeasuredDataset(Collection $results, Closure $value): array
    {
        return [
            'label' => __('general.not_measured'),
            'data' => $this->notMeasuredPointData($results, $value),
            'backgroundColor' => 'rgba(245, 158, 11, 0.85)',
            'borderColor' => 'rgba(245, 158, 11, 1)',
            'pointBackgroundColor' => 'rgba(245, 158, 11, 1)',
            'pointBorderColor' => 'rgba(245, 158, 11, 1)',
            'pointRadius' => $results->contains(fn (Result $result): bool => UniqueEgressValidator::isFailure($result)) ? 5 : 0,
            'pointStyle' => 'rectRot',
            'showLine' => false,
            'fill' => false,
            'speedtestLiteNotMeasured' => true,
            'order' => 0,
        ];
    }

    /**
     * @param  Collection<int, Result>  $results
     * @param  Closure(Result): mixed  $value
     * @param  array<string, string>  $colors
     * @return array<int, array<string, mixed>>
     */
    protected function measuredDatasets(Collection $results, string $label, Closure $value, array $colors): array
    {
        if (! $this->isAllProfilesChart()) {
            return [
                $this->lineDataset(
                    label: $label,
                    data: $results->map(fn (Result $result) => $result->status === ResultStatus::Completed ? $value($result) : null),
                    colors: $colors,
                    pointRadius: count($results) <= 24 ? 3 : 0,
                ),
            ];
        }

        return $this->profileGroups($results)
            ->map(fn (array $profile): array => $this->lineDataset(
                label: $profile['name'],
                data: $results->map(fn (Result $result) => $result->status === ResultStatus::Completed && $this->profileKey($result) === $profile['key'] ? $value($result) : null),
                colors: $this->profileColors($profile['index']),
                pointRadius: count($results) <= 24 ? 3 : 0,
                profileKey: $profile['key'],
                spanGaps: true,
            ))
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Result>  $results
     * @param  array<int, array{label: string, value: Closure, colors: array<string, string>}>  $series
     * @return array<int, array<string, mixed>>
     */
    protected function multiMetricDatasets(Collection $results, array $series): array
    {
        if (! $this->isAllProfilesChart()) {
            return collect($series)
                ->map(fn (array $metric): array => $this->lineDataset(
                    label: $metric['label'],
                    data: $results->map(fn (Result $result) => $result->status === ResultStatus::Completed ? $metric['value']($result) : null),
                    colors: $metric['colors'],
                    pointRadius: count($results) <= 24 ? 3 : 0,
                ))
                ->values()
                ->all();
        }

        return $this->profileGroups($results)
            ->flatMap(function (array $profile) use ($results, $series): array {
                $profileColors = $this->profileColors($profile['index']);

                return collect($series)
                    ->map(fn (array $metric, int $metricIndex): array => $this->lineDataset(
                        label: "{$profile['name']} {$metric['label']}",
                        data: $results->map(fn (Result $result) => $result->status === ResultStatus::Completed && $this->profileKey($result) === $profile['key'] ? $metric['value']($result) : null),
                        colors: $this->metricProfileColors($profileColors, $metricIndex),
                        pointRadius: count($results) <= 24 ? 3 : 0,
                        fill: $metricIndex === 0,
                        profileKey: $profile['key'],
                        spanGaps: true,
                    ))
                    ->all();
            })
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Result>  $results
     * @param  Closure(Result): mixed  $value
     * @return array<int, array<string, mixed>>
     */
    protected function profileSummaryDatasets(Collection $results, Closure $value): array
    {
        return $this->profileGroups($results)
            ->map(fn (array $profile): array => $this->lineDataset(
                label: $profile['name'],
                data: $results->map(fn (Result $result) => $result->status === ResultStatus::Completed && $this->profileKey($result) === $profile['key'] ? $value($result) : null),
                colors: $this->profileColors($profile['index']),
                pointRadius: count($results) <= 24 ? 3 : 0,
                profileKey: $profile['key'],
                spanGaps: true,
            ))
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Result>  $results
     * @return array<int, array<string, mixed>>
     */
    protected function averageDatasets(Collection $results, mixed $average): array
    {
        // All-profile charts already carry one line per ISP profile; a single average line adds clutter.
        if ($this->isAllProfilesChart() || $average === null) {
            return [];
        }

        return [
            [
                'label' => __('general.average'),
                'data' => array_fill(0, count($results), $average),
                'borderColor' => 'rgb(243, 7, 6, 1)',
                'pointBackgroundColor' => 'rgb(243, 7, 6, 1)',
                'fill' => false,
                'cubicInterpolationMode' => 'monotone',
                'tension' => 0.4,
                'pointRadius' => 0,
            ],
        ];
    }

    /**
     * @param  Collection<int, Result>  $results
     * @return array<int, array<string, mixed>>
     */
    protected function notMeasuredDatasets(Collection $results, Closure $value): array
    {
        if (! $this->isAllProfilesChart()) {
            return [$this->notMeasuredDataset($results, $value)];
        }

        return $this->profileGroups($results)
            ->map(function (array $profile) use ($results): array {
                $colors = $this->notMeasuredProfileColors($profile['index']);

                return [
                    'label' => "{$profile['name']} ".__('general.not_measured'),
                    'data' => $this->notMeasuredPointData($results, $value, $profile['key']),
                    'backgroundColor' => $colors['backgroundColor'],
                    'borderColor' => $colors['borderColor'],
                    'pointBackgroundColor' => $colors['borderColor'],
                    'pointBorderColor' => $colors['borderColor'],
                    'pointRadius' => $results->contains(fn (Result $result): bool => $this->profileKey($result) === $profile['key'] && UniqueEgressValidator::isFailure($result)) ? 5 : 0,
                    'pointStyle' => 'rectRot',
                    'showLine' => false,
                    'fill' => false,
                    'speedtestLiteProfileKey' => $profile['key'],
                    'speedtestLiteNotMeasured' => true,
                    'order' => 0,
                ];
            })
            ->values()
            ->all();
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
                        if (context.dataset.speedtestLiteNotMeasured) {
                            return `${context.dataset.label} - failover/egress collision`;
                        }

                        const label = context.dataset.label || '';
                        const value = context.formattedValue;

                        return label ? `${label}: ${value}` : value;
                    }
                JS),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function sharedLegendOptions(): array
    {
        return [
            'display' => true,
            'onClick' => RawJs::make(<<<'JS'
                function (event, legendItem, legend) {
                    const chart = legend.chart;
                    const clickedDataset = chart.data.datasets[legendItem.datasetIndex] || {};
                    const profileKey = clickedDataset.speedtestLiteProfileKey;

                    if (! profileKey) {
                        Chart.defaults.plugins.legend.onClick.call(this, event, legendItem, legend);
                        return;
                    }

                    const shouldHide = chart.isDatasetVisible(legendItem.datasetIndex);

                    chart.data.datasets.forEach(function (dataset, datasetIndex) {
                        if (dataset.speedtestLiteProfileKey === profileKey) {
                            chart.getDatasetMeta(datasetIndex).hidden = shouldHide;
                        }
                    });

                    chart.update();
                }
            JS),
        ];
    }

    /**
     * @param  Collection<int, Result>  $results
     * @return Collection<int, array{key: string, name: string, index: int}>
     */
    private function profileGroups(Collection $results): Collection
    {
        return $results
            ->filter(fn (Result $result): bool => filled($this->profileKey($result)))
            ->groupBy(fn (Result $result): string => $this->profileKey($result))
            ->map(fn (Collection $profileResults, string $key): array => [
                'key' => $key,
                'name' => $this->profileName($profileResults->first()),
            ])
            ->values()
            ->map(fn (array $profile, int $index): array => $profile + ['index' => $index]);
    }

    private function profileKey(Result $result): string
    {
        return (string) ($result->isp_profile_key ?: 'untagged');
    }

    private function profileName(?Result $result): string
    {
        if ($result instanceof Result && filled($result->isp_profile_name)) {
            return (string) $result->isp_profile_name;
        }

        if ($result instanceof Result && filled($result->isp_profile_key)) {
            return (string) $result->isp_profile_key;
        }

        return 'untagged';
    }

    /**
     * @param  Collection<int, mixed>  $data
     * @param  array<string, string>  $colors
     * @return array<string, mixed>
     */
    private function lineDataset(string $label, Collection $data, array $colors, int $pointRadius, bool $fill = true, ?string $profileKey = null, bool $spanGaps = false): array
    {
        $dataset = [
            'label' => $label,
            'data' => $data,
            'borderColor' => $colors['borderColor'],
            'backgroundColor' => $colors['backgroundColor'],
            'pointBackgroundColor' => $colors['pointBackgroundColor'],
            'fill' => $fill,
            'cubicInterpolationMode' => 'monotone',
            'tension' => 0.4,
            'pointRadius' => $pointRadius,
            'spanGaps' => $spanGaps,
        ];

        if ($profileKey !== null) {
            $dataset['speedtestLiteProfileKey'] = $profileKey;
        }

        return $dataset;
    }

    /**
     * @return Collection<int, mixed>
     */
    private function notMeasuredPointData(Collection $results, Closure $value, ?string $profileKey = null): Collection
    {
        $lastMeasuredValue = null;

        return $results->map(function (Result $result) use (&$lastMeasuredValue, $profileKey) {
            if ($profileKey !== null && $this->profileKey($result) !== $profileKey) {
                return null;
            }

            if ($result->status === ResultStatus::Completed) {
                $lastMeasuredValue = $value($result) ?? $lastMeasuredValue;

                return null;
            }

            if (UniqueEgressValidator::isFailure($result)) {
                return $lastMeasuredValue;
            }

            return null;
        });
    }

    /**
     * @return array<string, string>
     */
    private function profileColors(int $index): array
    {
        $palette = [
            ['14, 165, 233', '0.12'],
            ['168, 85, 247', '0.12'],
            ['34, 197, 94', '0.12'],
            ['249, 115, 22', '0.12'],
            ['236, 72, 153', '0.12'],
            ['20, 184, 166', '0.12'],
            ['234, 179, 8', '0.12'],
            ['99, 102, 241', '0.12'],
        ];

        [$rgb, $alpha] = $palette[$index % count($palette)];

        return [
            'borderColor' => "rgba({$rgb}, 1)",
            'backgroundColor' => "rgba({$rgb}, {$alpha})",
            'pointBackgroundColor' => "rgba({$rgb}, 1)",
        ];
    }

    /**
     * @param  array<string, string>  $profileColors
     * @return array<string, string>
     */
    private function metricProfileColors(array $profileColors, int $metricIndex): array
    {
        if ($metricIndex === 0) {
            return $profileColors;
        }

        return [
            'borderColor' => $profileColors['borderColor'],
            'backgroundColor' => 'rgba(0, 0, 0, 0)',
            'pointBackgroundColor' => $profileColors['pointBackgroundColor'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function notMeasuredProfileColors(int $index): array
    {
        $palette = [
            '245, 158, 11',
            '251, 191, 36',
            '249, 115, 22',
            '234, 179, 8',
            '251, 146, 60',
            '217, 119, 6',
        ];

        $rgb = $palette[$index % count($palette)];

        return [
            'backgroundColor' => "rgba({$rgb}, 0.85)",
            'borderColor' => "rgba({$rgb}, 1)",
        ];
    }
}
