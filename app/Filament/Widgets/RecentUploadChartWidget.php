<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasChartFilters;
use App\Helpers\Average;
use App\Helpers\Number;
use Filament\Widgets\ChartWidget;

class RecentUploadChartWidget extends ChartWidget
{
    use HasChartFilters;

    protected ?string $heading = null;

    public function getHeading(): ?string
    {
        return __('general.upload_mbps');
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
        $results = $this->dashboardChartResults(['upload']);
        $completedResults = $this->completedChartResults($results);

        return [
            'datasets' => array_merge(
                $this->measuredDatasets(
                    results: $results,
                    label: __('general.upload'),
                    value: fn ($item) => ! blank($item->upload) ? Number::bitsToMagnitude(bits: $item->upload_bits, precision: 2, magnitude: 'mbit') : null,
                    colors: [
                        'borderColor' => 'rgba(139, 92, 246)',
                        'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                        'pointBackgroundColor' => 'rgba(139, 92, 246)',
                    ],
                ),
                $this->averageDatasets($results, $completedResults->isNotEmpty() ? Average::averageUpload($completedResults) : null),
                $this->notMeasuredDatasets(
                    results: $results,
                    value: fn ($item) => ! blank($item->upload) ? Number::bitsToMagnitude(bits: $item->upload_bits, precision: 2, magnitude: 'mbit') : null,
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
