<?php

namespace App\Filament\Widgets\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasChartFilters
{
    public ?string $ispProfileKey = null;

    protected function getFilters(): ?array
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
}
