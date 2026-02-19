<script>
    (() => {
        if (window.__inventarioTableKeyboardSelectionLoaded) {
            return;
        }

        window.__inventarioTableKeyboardSelectionLoaded = true;

        const tableState = new WeakMap();
        let lastActiveTable = null;

        const getTableContainer = (element) => element?.closest?.('.fi-ta') ?? null;

        const isEditableElement = (element) => {
            if (! element || ! (element instanceof HTMLElement)) {
                return false;
            }

            if (element.isContentEditable) {
                return true;
            }

            const tagName = element.tagName.toLowerCase();

            if (tagName === 'textarea' || tagName === 'select') {
                return true;
            }

            if (tagName !== 'input') {
                return false;
            }

            const input = /** @type {HTMLInputElement} */ (element);
            const type = (input.type || '').toLowerCase();

            return ! ['checkbox', 'radio', 'button', 'submit'].includes(type);
        };

        const getState = (table) => {
            if (! tableState.has(table)) {
                tableState.set(table, {
                    anchorIndex: null,
                });
            }

            return tableState.get(table);
        };

        const getRecordCheckboxes = (table) =>
            Array.from(table.querySelectorAll('input.fi-ta-record-checkbox[type="checkbox"]:not(:disabled)'));

        const setCheckboxChecked = (checkbox, checked) => {
            if (checkbox.checked === checked) {
                return;
            }

            checkbox.click();
        };

        const focusCheckbox = (checkbox) => {
            checkbox.focus({ preventScroll: true });
            checkbox.scrollIntoView({ block: 'nearest', inline: 'nearest' });
        };

        const findCurrentIndex = (checkboxes) => {
            const focused = document.activeElement;

            if (focused instanceof HTMLInputElement) {
                const focusedIndex = checkboxes.indexOf(focused);

                if (focusedIndex !== -1) {
                    return focusedIndex;
                }
            }

            const checkedIndex = checkboxes.findIndex((checkbox) => checkbox.checked);

            return checkedIndex !== -1 ? checkedIndex : 0;
        };

        const selectRange = (checkboxes, from, to) => {
            const start = Math.min(from, to);
            const end = Math.max(from, to);

            for (let index = start; index <= end; index++) {
                setCheckboxChecked(checkboxes[index], true);
            }
        };

        document.addEventListener(
            'focusin',
            (event) => {
                const table = getTableContainer(event.target);

                if (table) {
                    lastActiveTable = table;
                }
            },
            true,
        );

        document.addEventListener(
            'click',
            (event) => {
                const checkbox = event.target instanceof Element
                    ? event.target.closest('input.fi-ta-record-checkbox[type="checkbox"]')
                    : null;

                if (! checkbox) {
                    const table = getTableContainer(event.target);

                    if (table) {
                        lastActiveTable = table;
                    }

                    return;
                }

                const table = getTableContainer(checkbox);

                if (! table) {
                    return;
                }

                lastActiveTable = table;

                const checkboxes = getRecordCheckboxes(table);
                const state = getState(table);
                const index = checkboxes.indexOf(checkbox);

                if (index === -1) {
                    return;
                }

                if (event.shiftKey && state.anchorIndex !== null) {
                    selectRange(checkboxes, state.anchorIndex, index);
                } else {
                    state.anchorIndex = index;
                }
            },
            true,
        );

        document.addEventListener(
            'keydown',
            (event) => {
                if (isEditableElement(event.target)) {
                    return;
                }

                const table = getTableContainer(event.target) ?? lastActiveTable;

                if (! table) {
                    return;
                }

                const checkboxes = getRecordCheckboxes(table);

                if (! checkboxes.length) {
                    return;
                }

                const state = getState(table);

                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'a') {
                    event.preventDefault();

                    checkboxes.forEach((checkbox) => setCheckboxChecked(checkbox, true));

                    const currentIndex = findCurrentIndex(checkboxes);
                    state.anchorIndex = currentIndex;
                    focusCheckbox(checkboxes[currentIndex]);

                    return;
                }

                if (event.key === 'Escape') {
                    event.preventDefault();

                    checkboxes.forEach((checkbox) => setCheckboxChecked(checkbox, false));
                    state.anchorIndex = null;

                    return;
                }

                const step = event.key === 'ArrowDown' ? 1 : event.key === 'ArrowUp' ? -1 : 0;

                if (! step) {
                    return;
                }

                event.preventDefault();

                const currentIndex = findCurrentIndex(checkboxes);
                const nextIndex = Math.max(0, Math.min(checkboxes.length - 1, currentIndex + step));

                if (event.shiftKey) {
                    if (state.anchorIndex === null) {
                        state.anchorIndex = currentIndex;
                    }

                    selectRange(checkboxes, state.anchorIndex, nextIndex);
                } else {
                    state.anchorIndex = nextIndex;
                }

                focusCheckbox(checkboxes[nextIndex]);
            },
            true,
        );
    })();
</script>
