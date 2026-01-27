@props(['active' => false])

<a {{ $attributes->merge([
    'class' => $active
        ? 'inline-flex items-center px-3 py-2 border-b-2 border-primary-500 text-sm font-medium leading-5 text-gray-900 dark:text-white focus:outline-none focus:border-primary-700 transition'
        : 'inline-flex items-center px-3 py-2 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition'
]) }}>
    {{ $slot }}
</a>
