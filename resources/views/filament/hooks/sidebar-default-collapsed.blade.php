<script>
    (() => {
        try {
            if (! window.matchMedia('(min-width: 1024px)').matches) {
                return;
            }

            const initKey = 'inventario_sidebar_init_v1';

            if (localStorage.getItem(initKey)) {
                return;
            }

            // Filament stores desktop sidebar state in `isOpen`.
            // We initialize it once as collapsed, then the user preference persists.
            localStorage.setItem('isOpen', 'false');
            localStorage.setItem(initKey, '1');
        } catch (_) {
            // no-op
        }
    })();
</script>
