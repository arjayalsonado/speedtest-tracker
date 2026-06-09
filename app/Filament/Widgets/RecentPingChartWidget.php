<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasChartFilters;
use App\Helpers\Average;
use Filament\Widgets\ChartWidget;

class RecentPingChartWidget extends ChartWidget
{
    use HasChartFilters;

    protected ?string $heading = null;

    public function getHeading(): ?string
    {
        return __('general.ping_ms');
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
        $results = $this->dashboardChartResults(['ping']);
        $completedResults = $this->completedChartResults($results);

        return [
            'datasets' => array_merge(
                $this->measuredDatasets(
                    results: $results,
                    label: __('general.ping'),
                    value: fn ($item) => $item->ping,
                    colors: [
                        'borderColor' => 'rgba(16, 185, 129)',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'pointBackgroundColor' => 'rgba(16, 185, 129)',
                    ],
                ),
                $this->averageDatasets($results, $completedResults->isNotEmpty() ? Average::averagePing($completedResults) : null),
                $this->notMeasuredDatasets(
                    results: $results,
                    value: fn ($item) => $item->ping,
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
