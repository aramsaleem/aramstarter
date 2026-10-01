<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="color-scheme" content="light dark" />

<title>{{ filled($title ?? null) ? $title.' · '.config('app.name') : config('app.name') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=geist:400,500,600,700|geist-mono:400,500|vazirmatn:400,500,600,700" rel="stylesheet" />

{{--
    Restore the theme (light unless the user picked dark or system) and the sidebar state before the page paints, so neither flashes.
    wire:navigate replaces the <html> attributes with the server's, so both are re-applied after every swap.
--}}
<script>
    window.appearance = {
        get() {
            try {
                return localStorage.getItem('appearance') || 'light';
            } catch (e) {
                return 'light';
            }
        },
        set(mode) {
            try {
                mode === 'light' ? localStorage.removeItem('appearance') : localStorage.setItem('appearance', mode);
            } catch (e) {}

            this.apply();
        },
        apply() {
            const mode = this.get();
            // Pages marked data-force-light (the public website) are designed for light only.
            const dark = ! ('forceLight' in document.documentElement.dataset)
                && (mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches));

            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
        },
    };

    window.sidebar = {
        collapsed() {
            try {
                return localStorage.getItem('sidebar') === 'collapsed';
            } catch (e) {
                return false;
            }
        },
        toggle() {
            try {
                localStorage.setItem('sidebar', this.collapsed() ? 'expanded' : 'collapsed');
            } catch (e) {}

            this.apply();
        },
        apply() {
            document.documentElement.dataset.sidebar = this.collapsed() ? 'collapsed' : 'expanded';
        },
    };

    window.appearance.apply();
    window.sidebar.apply();

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => window.appearance.apply());

    document.addEventListener('livewire:navigating', (event) => event.detail.onSwap(() => {
        window.appearance.apply();
        window.sidebar.apply();
    }));
</script>

@livewireStyles
@vite(['resources/css/app.css', 'resources/js/app.js'])
