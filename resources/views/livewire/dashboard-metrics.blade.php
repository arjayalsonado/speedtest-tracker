<div class="grid grid-cols-1 gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between col-span-full">
        <h2 class="flex items-center gap-x-2 text-base md:text-lg font-semibold text-zinc-900 dark:text-zinc-100">
            <x-tabler-chart-histogram class="size-5" />
            {{ __('general.metrics') }}
        </h2>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            @if (count($this->profileOptions) > 1)
                <label class="inline-flex items-center gap-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    <span>{{ __('general.isp_profile') }}</span>
                    <select
                        wire:model.live="ispProfileKey"
                        class="min-w-48 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    >
                        @foreach ($this->profileOptions as $key => $name)
                            <option value="{{ $key }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </label>
            @endif

            <label class="inline-flex items-center gap-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                <span>{{ __('general.chart_range') }}</span>
                <select
                    wire:model.live="chartRange"
                    class="min-w-40 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                >
                    @foreach ($this->chartRangeOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    @livewire(\App\Filament\Widgets\RecentDownloadChartWidget::class, ['ispProfileKey' => $ispProfileKey, 'filter' => $chartRange, 'showChartRangeFilter' => false], key('download-'.$ispProfileKey.'-'.$chartRange))

    @livewire(\App\Filament\Widgets\RecentUploadChartWidget::class, ['ispProfileKey' => $ispProfileKey, 'filter' => $chartRange, 'showChartRangeFilter' => false], key('upload-'.$ispProfileKey.'-'.$chartRange))

    @livewire(\App\Filament\Widgets\RecentPingChartWidget::class, ['ispProfileKey' => $ispProfileKey, 'filter' => $chartRange, 'showChartRangeFilter' => false], key('ping-'.$ispProfileKey.'-'.$chartRange))

    @livewire(\App\Filament\Widgets\RecentJitterChartWidget::class, ['ispProfileKey' => $ispProfileKey, 'filter' => $chartRange, 'showChartRangeFilter' => false], key('jitter-'.$ispProfileKey.'-'.$chartRange))

    @livewire(\App\Filament\Widgets\RecentDownloadLatencyChartWidget::class, ['ispProfileKey' => $ispProfileKey, 'filter' => $chartRange, 'showChartRangeFilter' => false], key('download-latency-'.$ispProfileKey.'-'.$chartRange))

    @livewire(\App\Filament\Widgets\RecentUploadLatencyChartWidget::class, ['ispProfileKey' => $ispProfileKey, 'filter' => $chartRange, 'showChartRangeFilter' => false], key('upload-latency-'.$ispProfileKey.'-'.$chartRange))
</div>
