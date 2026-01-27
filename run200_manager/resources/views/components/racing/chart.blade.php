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
        renderChart() {
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
</div>
