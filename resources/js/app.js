import Swal from 'sweetalert2';

/*
 * SweetAlert2 powers the toasts and confirmation dialogs sent by livewire-alert.
 * Every alert follows the current light/dark theme and uses the brand colors.
 */
const isDark = () => document.documentElement.classList.contains('dark');

window.Swal = new Proxy(Swal, {
    get(target, property, receiver) {
        if (property !== 'fire') {
            return Reflect.get(target, property, receiver);
        }

        return (...args) => {
            if (typeof args[0] !== 'object' || args[0] === null) {
                return target.fire(...args);
            }

            return target.fire({
                theme: isDark() ? 'dark' : 'light',
                confirmButtonColor: '#7c3aed',
                reverseButtons: true,
                ...args[0],
            });
        };
    },
});

/*
 * Toasts flashed to the session before a redirect (see InteractsWithAlerts::flashToast).
 * livewire:navigated fires on the first page load and after every wire:navigate visit.
 */
const showFlashedAlert = () => {
    const element = document.getElementById('flash-alert');

    if (!element) {
        return;
    }

    const { icon, title } = JSON.parse(element.textContent);
    element.remove();

    window.Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        // titleText is rendered as plain text; flashed titles can contain user input.
        titleText: title,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
    });
};

document.addEventListener('livewire:navigated', showFlashedAlert);

/*
 * Scroll reveal: elements with the "reveal" class fade in once they scroll into view.
 */
const revealOnScroll = () => {
    if (!('IntersectionObserver' in window)) {
        document.querySelectorAll('.reveal').forEach((element) => element.classList.add('is-visible'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12 },
    );

    document.querySelectorAll('.reveal:not(.is-visible)').forEach((element) => observer.observe(element));
};

document.addEventListener('livewire:navigated', revealOnScroll);

/*
 * navigator.clipboard only exists on HTTPS (and localhost), so fall back to
 * execCommand for local .test domains served over plain HTTP.
 */
window.copyToClipboard = async (text) => {
    if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
    }

    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    textarea.remove();
};

/*
 * Light / dark / system theme switcher. The theme itself is applied before the
 * first paint by the inline script in partials/head.blade.php.
 */
document.addEventListener('alpine:init', () => {
    /*
     * Command palette (resources/views/components/command-palette.blade.php).
     * Commands come from the server: { id, group, label, keywords, icon, type, value }.
     */
    window.Alpine.data('commandPalette', (commands) => ({
        open: false,
        query: '',
        active: 0,
        commands,
        shortcutLabel: /Mac|iPhone|iPad/.test(navigator.userAgent) ? '⌘K' : 'Ctrl K',

        get results() {
            const terms = this.query.toLowerCase().trim().split(/\s+/).filter(Boolean);

            return this.commands.filter((command) => {
                const haystack = `${command.label} ${command.group} ${command.keywords}`.toLowerCase();

                return terms.every((term) => haystack.includes(term));
            });
        },

        get groups() {
            const groups = [];

            this.results.forEach((command, index) => {
                let group = groups.find((candidate) => candidate.name === command.group);

                if (!group) {
                    group = { name: command.group, items: [] };
                    groups.push(group);
                }

                group.items.push({ ...command, index });
            });

            return groups;
        },

        shortcut(event) {
            const typing = ['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName) || event.target.isContentEditable;

            if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                this.open ? this.hide() : this.show();
            } else if (event.key === '/' && !typing && !this.open) {
                event.preventDefault();
                this.show();
            }
        },

        show() {
            this.query = '';
            this.active = 0;
            this.open = true;
            this.$nextTick(() => this.$refs.input.focus());
        },

        hide() {
            this.open = false;
        },

        move(step) {
            const count = this.results.length;

            if (count === 0) {
                return;
            }

            this.active = (this.active + step + count) % count;
            this.$nextTick(() => this.$refs.list.querySelector('[data-active="true"]')?.scrollIntoView({ block: 'nearest' }));
        },

        run(command = this.results[this.active]) {
            if (!command) {
                return;
            }

            this.hide();

            switch (command.type) {
                case 'link':
                    window.Livewire.navigate(command.value);
                    break;
                case 'theme':
                    window.Alpine.store('appearance').set(command.value);
                    break;
                case 'sidebar':
                    window.sidebar.toggle();
                    break;
                case 'locale':
                    this.$refs.localeInput.value = command.value;
                    this.$refs.locale.submit();
                    break;
                case 'logout':
                    this.$refs.logout.submit();
                    break;
            }
        },
    }));

    window.Alpine.store('appearance', {
        mode: window.appearance.get(),

        set(mode) {
            this.mode = mode;
            window.appearance.set(mode);
        },
    });
});
