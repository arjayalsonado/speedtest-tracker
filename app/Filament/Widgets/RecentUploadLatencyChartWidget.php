<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasChartFilters;
use Filament\Widgets\ChartWidget;

class RecentUploadLatencyChartWidget extends ChartWidget
{
    use HasChartFilters;

    protected ?string $heading = null;

    public function getHeading(): ?string
    {
        return __('general.upload_latency');
    }

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '250px';

    protected ?string $pollingInterval = '60s';

    public ?string $filter = null;

    public function mount(): void
    {
        $this->filter = $this->filter ?? config('speedtest.default_chart_range', '24h');
    }

    protected function getData(): array
    {
        $results = $this->dashboardChartResults(['data']);

        return [
            'datasets' => array_merge(
                $this->isAllProfilesChart()
                    ? $this->profileSummaryDatasets($results, fn ($item) => $item->upload_latency_iqm)
                    : $this->multiMetricDatasets($results, [
                        [
                            'label' => __('general.average_ms'),
                            'value' => fn ($item) => $item->upload_latency_iqm,
                            'colors' => [
                                'borderColor' => 'rgba(16, 185, 129)',
                                'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                                'pointBackgroundColor' => 'rgba(16, 185, 129)',
                            ],
                        ],
                        [
                            'label' => __('general.high_ms'),
                            'value' => fn ($item) => $item->upload_latency_high,
                            'colors' => [
                                'borderColor' => 'rgba(14, 165, 233)',
                                'backgroundColor' => 'rgba(14, 165, 233, 0.1)',
                                'pointBackgroundColor' => 'rgba(14, 165, 233)',
                            ],
                        ],
                        [
                            'label' => __('general.low_ms'),
                            'value' => fn ($item) => $item->upload_latency_low,
                            'colors' => [
                                'borderColor' => 'rgba(139, 92, 246)',
                                'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                                'pointBackgroundColor' => 'rgba(139, 92, 246)',
                            ],
                        ],
                    ]),
                $this->notMeasuredDatasets(
                    results: $results,
                    value: fn ($item) => $item->upload_latency_iqm,
                ),
            ),
            'labels' => $this->chartLabels($results),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => $this->sharedLegendOptions(),
                'tooltip' => $this->sharedTooltipOptions(),
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => config('app.chart_begin_at_zero'),
                    'grace' => 2,
                ],
                'notMeasured' => $this->notMeasuredScaleOptions(),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
