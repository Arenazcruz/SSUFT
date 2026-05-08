import './bootstrap';

function initPasswordVisibilityToggles() {
    const toggleButtons = Array.from(document.querySelectorAll('[data-password-toggle-button]'));

    if (!toggleButtons.length) {
        return;
    }

    toggleButtons.forEach((button) => {
        const targetId = button.dataset.target || '';
        const input = targetId ? document.getElementById(targetId) : null;

        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        const syncLabel = () => {
            const hidden = input.type === 'password';
            button.textContent = hidden ? 'Mostrar' : 'Ocultar';
            button.setAttribute('aria-label', hidden ? 'Mostrar contraseña' : 'Ocultar contraseña');
            button.setAttribute('aria-pressed', hidden ? 'false' : 'true');
        };

        button.addEventListener('click', () => {
            input.type = input.type === 'password' ? 'text' : 'password';
            syncLabel();
            input.focus({ preventScroll: true });
        });

        syncLabel();
    });
}

function initCollectionFilter({
    root,
    inputSelector,
    itemSelector,
    emptySelector,
    countSelector,
    chipSelector = null,
    getMatch,
}) {
    const searchInput = inputSelector ? root.querySelector(inputSelector) : null;
    const emptyState = emptySelector ? root.querySelector(emptySelector) : null;
    const countNode = countSelector ? root.querySelector(countSelector) : null;
    const items = Array.from(root.querySelectorAll(itemSelector));
    const chips = chipSelector ? Array.from(root.querySelectorAll(chipSelector)) : [];

    if (!items.length) {
        return;
    }

    const apply = () => {
        const term = (searchInput?.value || '').trim().toLowerCase();
        const activeChip = chips.find((chip) => chip.classList.contains('is-active'));
        const activeFilter = activeChip?.dataset.meetingFilter || 'all';

        let visible = 0;

        items.forEach((item) => {
            const isMatch = getMatch({ item, term, activeFilter });
            item.hidden = !isMatch;

            if (isMatch) {
                visible += 1;
            }
        });

        if (countNode) {
            countNode.textContent = String(visible);
        }

        if (emptyState) {
            emptyState.hidden = visible !== 0;
        }
    };

    if (searchInput) {
        searchInput.addEventListener('input', apply);
    }

    chips.forEach((chip) => {
        chip.addEventListener('click', () => {
            chips.forEach((item) => item.classList.remove('is-active'));
            chip.classList.add('is-active');
            apply();
        });
    });

    apply();
}

function initAdminDashboard() {
    const root = document.querySelector('[data-admin-dashboard]');

    if (!root) {
        return;
    }

    initCollectionFilter({
        root,
        inputSelector: '[data-meeting-search]',
        itemSelector: '[data-meeting-card]',
        emptySelector: '[data-meeting-empty]',
        countSelector: '[data-meeting-visible-count]',
        chipSelector: '[data-meeting-filter]',
        getMatch: ({ item, term, activeFilter }) => {
            const state = item.dataset.state || '';
            const haystack = item.dataset.search || '';
            const matchesFilter = activeFilter === 'all' || state === activeFilter;
            const matchesTerm = !term || haystack.includes(term);

            return matchesFilter && matchesTerm;
        },
    });

    initCollectionFilter({
        root,
        inputSelector: '[data-user-search]',
        itemSelector: '[data-user-card]',
        emptySelector: '[data-user-empty]',
        countSelector: '[data-user-visible-count]',
        getMatch: ({ item, term }) => {
            const haystack = item.dataset.search || '';

            return !term || haystack.includes(term);
        },
    });

    initUserFormChangeIndicators(root);
}

function initUserFormChangeIndicators(root) {
    const forms = Array.from(root.querySelectorAll('[data-user-form]'));

    if (!forms.length) {
        return;
    }

    forms.forEach((form) => {
        const saveButton = form.querySelector('[data-user-save-btn]');

        if (!(saveButton instanceof HTMLButtonElement)) {
            return;
        }

        const controls = Array.from(form.querySelectorAll('input, select, textarea')).filter((control) => {
            if (!(control instanceof HTMLInputElement || control instanceof HTMLSelectElement || control instanceof HTMLTextAreaElement)) {
                return false;
            }

            if (control.disabled || !control.name) {
                return false;
            }

            if (control instanceof HTMLInputElement) {
                return !['hidden', 'submit', 'button'].includes(control.type);
            }

            return true;
        });

        if (!controls.length) {
            return;
        }

        const snapshot = () => JSON.stringify(
            controls.map((control) => {
                if (control instanceof HTMLInputElement && control.type === 'checkbox') {
                    return [control.name, control.checked ? '1' : '0'];
                }

                return [control.name, control.value];
            })
        );

        const initialState = snapshot();

        const sync = () => {
            const hasChanges = snapshot() !== initialState;
            saveButton.classList.toggle('hidden', !hasChanges);
        };

        controls.forEach((control) => {
            control.addEventListener('input', sync);
            control.addEventListener('change', sync);
        });

        sync();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initPasswordVisibilityToggles();
        initAdminDashboard();
    }, { once: true });
} else {
    initPasswordVisibilityToggles();
    initAdminDashboard();
}
