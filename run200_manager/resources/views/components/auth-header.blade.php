@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <h1 class="text-2xl font-bold text-carbon-900 dark:text-white">{{ $title }}</h1>
    <p class="mt-2 text-sm text-carbon-600 dark:text-carbon-400">{{ $description }}</p>
</div>
