@props([
    'steps' => [],
    'currentStep' => 1,
])

<div {{ $attributes->merge(['class' => 'stepper-racing']) }}>
    @foreach($steps as $index => $step)
        @php
            $stepNumber = $index + 1;
            $isCompleted = $stepNumber < $currentStep;
            $isActive = $stepNumber === $currentStep;
        @endphp

        <div class="stepper-racing-step">
            {{-- Circle --}}
            <div @class([
                'stepper-racing-circle',
                'stepper-racing-circle-completed' => $isCompleted,
                'stepper-racing-circle-active' => $isActive,
            ])>
                @if($isCompleted)
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                @else
                    {{ $stepNumber }}
                @endif
            </div>

            {{-- Line (except for last step) --}}
            @if($index < count($steps) - 1)
                <div @class([
                    'stepper-racing-line',
                    'stepper-racing-line-completed' => $isCompleted,
                ])></div>
            @endif
        </div>
    @endforeach
</div>

{{-- Step labels (optional) --}}
@if(count($steps) > 0 && isset($steps[0]['label']))
    <div class="flex justify-between mt-2 px-2">
        @foreach($steps as $index => $step)
            @php
                $stepNumber = $index + 1;
                $isActive = $stepNumber === $currentStep;
            @endphp
            <span @class([
                'text-xs text-center flex-1',
                'text-racing-red-500 font-medium' => $isActive,
                'text-carbon-500' => !$isActive,
            ])>
                {{ $step['label'] ?? '' }}
            </span>
        @endforeach
    </div>
@endif
