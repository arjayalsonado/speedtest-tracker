<?php

namespace App\Filament\Widgets;

use App\Enums\ResultStatus;
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
            'datasets' => [
                [
                    'label' => __('general.upload'),
                    'data' => $results->map(fn ($item) => $item->status === ResultStatus::Completed && ! blank($item->upload) ? Number::bitsToMagnitude(bits: $item->upload_bits, precision: 2, magnitude: 'mbit') : null),
                    'borderColor' => 'rgba(139, 92, 246)',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                    'pointBackgroundColor' => 'rgba(139, 92, 246)',
                    'fill' => true,
                    'cubicInterpolationMode' => 'monotone',
                    'tension' => 0.4,
                    'pointRadius' => count($results) <= 24 ? 3 : 0,
                ],
                [
                    'label' => __('general.average'),
                    'data' => array_fill(0, count($results), Average::averageUpload($completedResults)),
                    'borderColor' => 'rgb(243, 7, 6, 1)',
                    'pointBackgroundColor' => 'rgb(243, 7, 6, 1)',
                    'fill' => false,
                    'cubicInterpolationMode' => 'monotone',
                    'tension' => 0.4,
                    'pointRadius' => 0,
                ],
                $this->notMeasuredDataset($results),
            ],
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
