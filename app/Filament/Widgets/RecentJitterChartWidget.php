<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasChartFilters;
use Filament\Widgets\ChartWidget;

class RecentJitterChartWidget extends ChartWidget
{
    use HasChartFilters;

    protected ?string $heading = null;

    public function getHeading(): ?string
    {
        return __('general.jitter');
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
                $this->multiMetricDatasets($results, [
                    [
                        'label' => __('general.download_ms'),
                        'value' => fn ($item) => $item->download_jitter,
                        'colors' => [
                            'borderColor' => 'rgba(14, 165, 233)',
                            'backgroundColor' => 'rgba(14, 165, 233, 0.1)',
                            'pointBackgroundColor' => 'rgba(14, 165, 233)',
                        ],
                    ],
                    [
                        'label' => __('general.upload_ms'),
                        'value' => fn ($item) => $item->upload_jitter,
                        'colors' => [
                            'borderColor' => 'rgba(139, 92, 246)',
                            'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                            'pointBackgroundColor' => 'rgba(139, 92, 246)',
                        ],
                    ],
                    [
                        'label' => __('general.ping_ms_label'),
                        'value' => fn ($item) => $item->ping_jitter,
                        'colors' => [
                            'borderColor' => 'rgba(16, 185, 129)',
                            'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                            'pointBackgroundColor' => 'rgba(16, 185, 129)',
                        ],
                    ],
                ]),
                $this->notMeasuredDatasets($results),
            ),
            'labels' => $this->chartLabels($results),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
                'tooltip' => $this->sharedTooltipOptions(),
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => config('app.chart_begin_at_zero'),
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
