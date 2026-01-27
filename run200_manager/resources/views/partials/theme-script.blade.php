{{--
    Immediate Theme Application Script
    This script MUST be placed in the <head> BEFORE any CSS to prevent flash of wrong theme.

    IMPORTANT: Dark mode is FORCED because the Racing Design System uses text-white
    extensively which would be unreadable in light mode. Light mode will be supported
    in a future update when all components are migrated to use adaptive text colors.
--}}
<script>
    // Force dark mode immediately to prevent flash and ensure readability
    (function() {
        document.documentElement.classList.add('dark');
        if (!localStorage.getItem('theme')) {
            localStorage.setItem('theme', 'dark');
        }
    })();
</script>
