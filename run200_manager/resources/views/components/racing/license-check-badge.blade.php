@props(['registration' => null, 'state' => null])

@php
    $state ??= $registration->licenseCheckState();
    [$label, $classes] = match ($state) {
        'valid' => ['Licence RCCR vérifiée', 'bg-status-success/20 text-status-success border-status-success/40'],
        'invalid' => ['Alerte : licence non conforme', 'bg-status-danger/20 text-status-danger border-status-danger/40'],
        'needs_review' => ['Licence à examiner', 'bg-status-warning/20 text-status-warning border-status-warning/40'],
        'stale' => ['Licence à recontrôler', 'bg-status-warning/20 text-status-warning border-status-warning/40'],
        'not_checked' => ['Licence à contrôler', 'bg-carbon-700 text-carbon-200 border-carbon-600'],
        default => ['Licence : sans objet', 'bg-carbon-800 text-carbon-400 border-carbon-700'],
    };
@endphp

<span {{ $attributes->class("inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-semibold leading-tight $classes") }}>
    {{ $label }}
</span>
