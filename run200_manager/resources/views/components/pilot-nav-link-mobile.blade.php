@props(['active' => false])

<a {{ $attributes->merge([
    'class' => $active
        ? 'block pl-3 pr-4 py-2 border-l-4 border-primary-400 text-base font-medium text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/20 focus:outline-none focus:bg-primary-100 dark:focus:bg-primary-900/40 transition'
        : 'block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none focus:text-gray-800 dark:focus:text-gray-200 focus:bg-gray-50 dark:focus:bg-gray-700 focus:border-gray-300 dark:focus:border-gray-600 transition'
]) }}>
    {{ $slot }}
</a>
