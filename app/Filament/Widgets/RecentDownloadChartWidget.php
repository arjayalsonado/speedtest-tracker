<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasChartFilters;
use App\Helpers\Average;
use App\Helpers\Number;
use Filament\Widgets\ChartWidget;

class RecentDownloadChartWidget extends ChartWidget
{
    use HasChartFilters;

    protected ?string $heading = null;

    public function getHeading(): ?string
    {
        return __('general.download_mbps');
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
        $results = $this->dashboardChartResults(['download']);
        $completedResults = $this->completedChartResults($results);

        return [
            'datasets' => array_merge(
                $this->measuredDatasets(
                    results: $results,
                    label: __('general.download'),
                    value: fn ($item) => ! blank($item->download) ? Number::bitsToMagnitude(bits: $item->download_bits, precision: 2, magnitude: 'mbit') : null,
                    colors: [
                        'borderColor' => 'rgba(14, 165, 233)',
                        'backgroundColor' => 'rgba(14, 165, 233, 0.1)',
                        'pointBackgroundColor' => 'rgba(14, 165, 233)',
                    ],
                ),
                $this->averageDatasets($results, $completedResults->isNotEmpty() ? Average::averageDownload($completedResults) : null),
                $this->notMeasuredDatasets(
                    results: $results,
                    value: fn ($item) => ! blank($item->download) ? Number::bitsToMagnitude(bits: $item->download_bits, precision: 2, magnitude: 'mbit') : null,
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
