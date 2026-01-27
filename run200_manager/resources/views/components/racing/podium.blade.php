@props([
    'first' => null,  // ['name' => '', 'points' => '', 'avatar' => '']
    'second' => null,
    'third' => null,
])

<div {{ $attributes->merge(['class' => 'podium-racing']) }}>
    {{-- 2nd Place --}}
    @if($second)
        <div class="podium-racing-place">
            <div class="podium-racing-avatar bg-carbon-500">
                {{ $second['avatar'] ?? substr($second['name'] ?? '?', 0, 2) }}
            </div>
            <div class="podium-racing-name">{{ Str::limit($second['name'] ?? '', 12) }}</div>
            <div class="podium-racing-points">{{ $second['points'] ?? 0 }} pts</div>
            <div class="podium-racing-block podium-racing-block-2">🥈 2</div>
        </div>
    @endif

    {{-- 1st Place --}}
    @if($first)
        <div class="podium-racing-place">
            <div class="podium-racing-crown">👑</div>
            <div class="podium-racing-avatar">
                {{ $first['avatar'] ?? substr($first['name'] ?? '?', 0, 2) }}
            </div>
            <div class="podium-racing-name">{{ Str::limit($first['name'] ?? '', 12) }}</div>
            <div class="podium-racing-points">{{ $first['points'] ?? 0 }} pts</div>
            <div class="podium-racing-block podium-racing-block-1">🥇 1</div>
        </div>
    @endif

    {{-- 3rd Place --}}
    @if($third)
        <div class="podium-racing-place">
            <div class="podium-racing-avatar bg-amber-700">
                {{ $third['avatar'] ?? substr($third['name'] ?? '?', 0, 2) }}
            </div>
            <div class="podium-racing-name">{{ Str::limit($third['name'] ?? '', 12) }}</div>
            <div class="podium-racing-points">{{ $third['points'] ?? 0 }} pts</div>
            <div class="podium-racing-block podium-racing-block-3">🥉 3</div>
        </div>
    @endif
</div>
