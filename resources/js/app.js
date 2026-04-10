import './bootstrap';

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
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminDashboard, { once: true });
} else {
    initAdminDashboard();
}
