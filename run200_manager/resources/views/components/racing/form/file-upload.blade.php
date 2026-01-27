{{--
    Racing File Upload Component
    A styled file upload with drag & drop, preview, and progress
--}}
@props([
    'name' => '',
    'label' => null,
    'accept' => null,
    'multiple' => false,
    'maxSize' => 5, // MB
    'hint' => null,
    'preview' => true,
    'required' => false,
])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $wireModel = $attributes->whereStartsWith('wire:model')->first();

    $acceptMap = [
        'image' => 'image/*',
        'images' => 'image/*',
        'pdf' => 'application/pdf',
        'document' => '.pdf,.doc,.docx',
        'documents' => '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx',
    ];
    $acceptValue = $acceptMap[$accept] ?? $accept;
@endphp

<div
    {{ $attributes->only('class')->merge(['class' => 'racing-file-upload-wrapper']) }}
    x-data="{
        isDragging: false,
        files: [],
        previews: [],
        maxSize: {{ $maxSize }},
        handleFiles(fileList) {
            const newFiles = Array.from(fileList);
            for (const file of newFiles) {
                if (file.size > this.maxSize * 1024 * 1024) {
                    alert(`Le fichier ${file.name} dépasse la taille maximale de ${this.maxSize}MB`);
                    continue;
                }
                @if(!$multiple)
                    this.files = [file];
                    this.previews = [];
                @else
                    this.files.push(file);
                @endif

                // Generate preview for images
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        @if(!$multiple)
                            this.previews = [{ name: file.name, url: e.target.result, type: 'image' }];
                        @else
                            this.previews.push({ name: file.name, url: e.target.result, type: 'image' });
                        @endif
                    };
                    reader.readAsDataURL(file);
                } else {
                    @if(!$multiple)
                        this.previews = [{ name: file.name, url: null, type: file.type }];
                    @else
                        this.previews.push({ name: file.name, url: null, type: file.type });
                    @endif
                }
            }
        },
        removeFile(index) {
            this.files.splice(index, 1);
            this.previews.splice(index, 1);
        },
        getFileIcon(type) {
            if (type.includes('pdf')) return '📄';
            if (type.includes('word') || type.includes('document')) return '📝';
            if (type.includes('sheet') || type.includes('excel')) return '📊';
            if (type.includes('image')) return '🖼️';
            return '📎';
        }
    }"
    x-on:dragover.prevent="isDragging = true"
    x-on:dragleave.prevent="isDragging = false"
    x-on:drop.prevent="isDragging = false; handleFiles($event.dataTransfer.files)"
>
    {{-- Label --}}
    @if($label)
        <label class="block text-sm font-medium text-carbon-700 dark:text-carbon-300 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-racing-red-500 ml-0.5">*</span>
            @endif
        </label>
    @endif

    {{-- Drop Zone --}}
    <div
        :class="{
            'border-racing-red-500 bg-racing-red-50 dark:bg-racing-red-900/20': isDragging,
            'border-carbon-300 dark:border-carbon-700 hover:border-racing-red-400': !isDragging,
            'border-status-danger': {{ $hasError ? 'true' : 'false' }}
        }"
        class="relative border-2 border-dashed rounded-xl transition-all duration-200 ease-out"
    >
        <input
            type="file"
            name="{{ $name }}"
            id="{{ $id }}"
            @if($wireModel) wire:model="{{ $wireModel }}" @endif
            @if($acceptValue) accept="{{ $acceptValue }}" @endif
            @if($multiple) multiple @endif
            @if($required) required @endif
            x-on:change="handleFiles($event.target.files)"
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
        />

        {{-- Upload Area --}}
        <div class="p-6 text-center" x-show="previews.length === 0">
            {{-- Icon --}}
            <div class="mx-auto w-12 h-12 rounded-full bg-carbon-100 dark:bg-carbon-800 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-carbon-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
            </div>

            {{-- Text --}}
            <p class="text-sm text-carbon-600 dark:text-carbon-400">
                <span class="font-medium text-racing-red-500">Cliquez pour choisir</span>
                ou glissez-déposez
            </p>
            <p class="text-xs text-carbon-500 dark:text-carbon-500 mt-1">
                @if($accept)
                    {{ strtoupper(str_replace(['image/*', 'application/'], ['Images', ''], $acceptValue)) }}
                @else
                    Tous les fichiers
                @endif
                (max {{ $maxSize }}MB)
            </p>
        </div>

        {{-- Preview Area --}}
        @if($preview)
            <div x-show="previews.length > 0" class="p-4">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    <template x-for="(preview, index) in previews" :key="index">
                        <div class="relative group rounded-lg overflow-hidden bg-carbon-100 dark:bg-carbon-800">
                            {{-- Image Preview --}}
                            <template x-if="preview.type === 'image'">
                                <img :src="preview.url" :alt="preview.name" class="w-full h-24 object-cover"/>
                            </template>

                            {{-- File Icon Preview --}}
                            <template x-if="preview.type !== 'image'">
                                <div class="w-full h-24 flex flex-col items-center justify-center">
                                    <span class="text-3xl" x-text="getFileIcon(preview.type)"></span>
                                    <span class="text-xs text-carbon-500 mt-1 truncate max-w-full px-2" x-text="preview.name"></span>
                                </div>
                            </template>

                            {{-- Remove Button --}}
                            <button
                                type="button"
                                x-on:click.stop="removeFile(index)"
                                class="absolute top-1 right-1 w-6 h-6 rounded-full bg-red-500 text-white opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center z-20"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>

                {{-- Add More Button (if multiple) --}}
                @if($multiple)
                    <div class="mt-3 text-center">
                        <span class="text-xs text-carbon-500">Cliquez ou glissez pour ajouter plus de fichiers</span>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Hint Text --}}
    @if($hint && !$hasError)
        <p class="mt-1.5 text-xs text-carbon-500 dark:text-carbon-400">{{ $hint }}</p>
    @endif

    {{-- Error Message --}}
    @if($hasError)
        <p class="mt-1.5 text-xs text-status-danger flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $errors->first($name) }}
        </p>
    @endif
</div>
