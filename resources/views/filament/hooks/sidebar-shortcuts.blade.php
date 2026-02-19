<script>
    (() => {
        if (window.__inventarioSidebarShortcutsLoaded) {
            return;
        }

        window.__inventarioSidebarShortcutsLoaded = true;

        const isEditableTarget = (element) => {
            if (! element || ! (element instanceof HTMLElement)) {
                return false;
            }

            if (element.isContentEditable) {
                return true;
            }

            const tag = element.tagName.toLowerCase();

            if (tag === 'textarea' || tag === 'select') {
                return true;
            }

            if (tag !== 'input') {
                return false;
            }

            const inputType = (element.type || '').toLowerCase();

            return ! ['checkbox', 'radio', 'button', 'submit'].includes(inputType);
        };

        document.addEventListener('keydown', (event) => {
            const isToggleShortcut =
                (event.metaKey || event.ctrlKey) &&
                ! event.altKey &&
                event.key.toLowerCase() === 'b';

            if (! isToggleShortcut || isEditableTarget(event.target)) {
                return;
            }

            const sidebarStore = window.Alpine?.store?.('sidebar');

            if (! sidebarStore) {
                return;
            }

            event.preventDefault();

            if (sidebarStore.isOpen) {
                sidebarStore.close();
            } else {
                sidebarStore.open();
            }
        });
    })();
</script>
