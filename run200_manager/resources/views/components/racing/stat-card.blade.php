@props([
    'value',
    'label',
    'icon' => null,
    'trend' => null, // up, down, neutral
    'trendValue' => null,
    'href' => null,
    'highlight' => false,
])

@php
    $trendColors = match($trend) {
        'up' => 'text-status-success',
        'down' => 'text-status-danger',
        default => 'text-carbon-500',
    };

    $trendIcons = match($trend) {
        'up' => '↑',
        'down' => '↓',
        default => '─',
    };

    $highlightClass = $highlight
        ? 'bg-gradient-to-br from-racing-red-50 to-white dark:from-racing-red-950/30 dark:to-carbon-900 border-racing-red-200 dark:border-racing-red-900'
        : '';
@endphp

<div {{ $attributes->merge(['class' => "stat-card-racing {$highlightClass}"]) }}>
    @if($icon)
        <div class="stat-card-racing-icon">
            {!! $icon !!}
        </div>
    @endif

    <div class="stat-card-racing-value animate-count-up">
        {{ $value }}
    </div>

    <div class="stat-card-racing-label">
        {{ $label }}
    </div>

    @if($trend && $trendValue)
        <div class="mt-2 text-sm {{ $trendColors }}">
            <span>{{ $trendIcons }}</span>
            <span>{{ $trendValue }}</span>
        </div>
    @endif

    @if($href)
        <a href="{{ $href }}" class="mt-3 inline-block text-sm text-racing-red-500 hover:text-racing-red-600 font-medium">
            Voir détails →
        </a>
    @endif
</div>
