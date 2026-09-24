@props([
    'id',
    'type' => 'line', // line, bar, doughnut, horizontalBar
    'height' => '300px',
    'labels' => [],
    'datasets' => [],
    'colors' => null,
])

@php
    $chartId = $id ?? 'chart-' . uniqid();
    $jsonLabels = json_encode($labels);
    $jsonDatasets = json_encode($datasets);
    $jsonColors = $colors ? json_encode($colors) : 'null';
@endphp

<div
    x-data="{
        chart: null,
        chartError: false,
        init() {
            this.$nextTick(() => {
                this.renderChart();
            });

            // Re-render on Livewire updates
            Livewire.hook('morph.updated', ({ el }) => {
                if (el.contains(this.$el) || el === this.$el) {
                    this.$nextTick(() => this.renderChart());
                }
            });
        },
        async renderChart() {
            try {
                await window.loadCharts();
            } catch (error) {
                console.error('Unable to load chart module:', error);
                this.chartError = true;
                return;
            }

            if (!this.$el.isConnected) return;
            this.chartError = false;

            const labels = {{ Js::from($labels) }};
            const datasets = {{ Js::from($datasets) }};
            const colors = {{ $colors ? Js::from($colors) : 'null' }};
            const type = '{{ $type }}';

            if (type === 'line') {
                this.chart = window.createLineChart('{{ $chartId }}', labels, datasets);
            } else if (type === 'doughnut') {
                // Pour doughnut, datasets est un simple array de valeurs
                this.chart = window.createDoughnutChart('{{ $chartId }}', labels, datasets, colors);
            } else if (type === 'bar') {
                this.chart = window.createBarChart('{{ $chartId }}', labels, datasets);
            } else if (type === 'horizontalBar') {
                this.chart = window.createHorizontalBarChart('{{ $chartId }}', labels, datasets, colors ? colors[0] : null);
            }
        }
    }"
    {{ $attributes->merge(['class' => 'relative']) }}
    style="height: {{ $height }};"
>
    <canvas id="{{ $chartId }}" class="w-full h-full"></canvas>
    <p x-cloak x-show="chartError" class="absolute inset-0 flex items-center justify-center bg-carbon-900/90 text-center text-sm text-carbon-400" role="status">
        Impossible de charger le graphique. Rechargez la page.
    </p>
</div>
