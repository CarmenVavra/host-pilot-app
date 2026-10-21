/*
 * HostPilot – progressive enhancements. Every feature also works without
 * JavaScript (plain forms and links); these handlers only make it smoother.
 */

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/* Confirmation before destructive actions: <form data-confirm="…"> opens #confirm-dialog */
function initConfirm() {
    const dialog = document.querySelector('[data-confirm-dialog]');
    if (!dialog || typeof dialog.showModal !== 'function') return;

    const message = dialog.querySelector('[data-confirm-message]');
    const accept = dialog.querySelector('[data-confirm-accept]');
    let pendingForm = null;
    let opener = null;

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-confirm]');
        if (!form) return;
        event.preventDefault();

        pendingForm = form;
        opener = event.submitter ?? document.activeElement;
        message.textContent = form.dataset.confirm;
        accept.textContent = form.dataset.confirmButton || 'Löschen';
        dialog.returnValue = '';
        dialog.showModal();
    });

    // Click on the backdrop cancels.
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close('cancel');
    });

    dialog.addEventListener('close', () => {
        const form = pendingForm;
        pendingForm = null;

        if (dialog.returnValue === 'confirm' && form) {
            // Native submit() skips the submit event, so the dialog does not reopen.
            HTMLFormElement.prototype.submit.call(form);
            return;
        }

        opener?.focus?.();
    });
}

/* Toast: auto-hide after a few seconds, closable */
function initToast() {
    const toast = document.querySelector('[data-toast]');
    if (!toast) return;
    const hide = () => toast.classList.add('is-hidden');
    toast.querySelector('[data-toast-close]')?.addEventListener('click', hide);
    window.setTimeout(hide, 4500);
}

/* Shopping list: toggle «erledigt» without reload, live counters */
function updateShoppingCounters() {
    const items = [...document.querySelectorAll('[data-shop-item]')];
    const done = items.filter((item) => item.classList.contains('is-done')).length;
    const total = items.length;
    const percent = total ? Math.round((done / total) * 100) : 0;

    document.querySelectorAll('[data-shop-done]').forEach((el) => (el.textContent = done));
    document.querySelectorAll('[data-shop-progress]').forEach((el) => (el.style.width = `${percent}%`));
    document.querySelectorAll('[data-shop-progressbar]').forEach((el) => el.setAttribute('aria-valuenow', done));

    document.querySelectorAll('[data-category]').forEach((category) => {
        const rows = [...category.querySelectorAll('[data-shop-item]')];
        const rowsDone = rows.filter((row) => row.classList.contains('is-done')).length;
        const counter = category.querySelector('[data-category-count]');
        if (counter) counter.textContent = `${rowsDone}/${rows.length}`;
    });
}

function initShoppingToggle() {
    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-toggle-form]');
        if (!form) return;
        event.preventDefault();

        const row = form.closest('[data-shop-item]');
        const button = form.querySelector('button');
        const nextDone = !row.classList.contains('is-done');

        // Optimistic update, reverted on failure.
        row.classList.toggle('is-done', nextDone);
        button.setAttribute('aria-pressed', String(nextDone));
        updateShoppingCounters();

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: new FormData(form),
            });
            if (!response.ok) throw new Error(String(response.status));
            const data = await response.json();
            row.classList.toggle('is-done', data.done);
            button.setAttribute('aria-pressed', String(data.done));
        } catch {
            row.classList.toggle('is-done', !nextDone);
            button.setAttribute('aria-pressed', String(!nextDone));
        }
        updateShoppingCounters();
    });
}

/* Agenda (phone): swipe left or long-press reveals «Ändern» / «Löschen» */
function initSwipe() {
    const rows = document.querySelectorAll('[data-swipe]');
    if (!rows.length) return;

    const OPEN = 132;
    const closeAll = (except) => rows.forEach((row) => row !== except && row.classList.remove('is-open'));

    rows.forEach((row) => {
        const card = row.querySelector('.agenda-card');
        let startX = 0;
        let startY = 0;
        let dx = 0;
        let dragging = false;
        let pressTimer = null;

        const reset = () => {
            row.classList.remove('is-dragging');
            card.style.transform = '';
            window.clearTimeout(pressTimer);
        };

        card.addEventListener('touchstart', (e) => {
            const t = e.touches[0];
            startX = t.clientX;
            startY = t.clientY;
            dx = 0;
            dragging = false;
            pressTimer = window.setTimeout(() => {
                closeAll(row);
                row.classList.toggle('is-open');
                navigator.vibrate?.(15);
            }, 550);
        }, { passive: true });

        card.addEventListener('touchmove', (e) => {
            const t = e.touches[0];
            const mx = t.clientX - startX;
            const my = t.clientY - startY;
            if (Math.abs(mx) > 8 || Math.abs(my) > 8) window.clearTimeout(pressTimer);
            if (!dragging && Math.abs(mx) > 10 && Math.abs(mx) > Math.abs(my)) {
                dragging = true;
                row.classList.add('is-dragging');
                closeAll(row);
            }
            if (!dragging) return;
            // Closed: follow the finger to the left. Open: follow it back to the right.
            dx = row.classList.contains('is-open')
                ? Math.max(0, Math.min(OPEN, mx))
                : Math.max(-OPEN - 20, Math.min(0, mx));
            card.style.transform = `translateX(${dx}px)`;
        }, { passive: true });

        card.addEventListener('touchend', () => {
            if (dragging) {
                const open = row.classList.contains('is-open');
                row.classList.toggle('is-open', open ? dx < OPEN / 2 : dx < -OPEN / 2);
            }
            reset();
        });
        card.addEventListener('touchcancel', reset);

        // Keyboard users: focusing an action reveals it.
        row.querySelectorAll('.swipe__action').forEach((action) => {
            action.addEventListener('focus', () => {
                closeAll(row);
                row.classList.add('is-open');
            });
        });
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-swipe]')) closeAll(null);
    });
}

/* Recipe form: add / remove ingredient rows */
function initIngredientRows() {
    const container = document.querySelector('[data-ingredient-rows]');
    const template = document.querySelector('#ingredient-row-template');
    const add = document.querySelector('[data-add-ingredient]');
    if (!container || !template || !add) return;

    let index = Number(container.dataset.next || container.children.length);

    add.addEventListener('click', () => {
        const html = template.innerHTML.replaceAll('__INDEX__', String(index++));
        container.insertAdjacentHTML('beforeend', html);
        container.lastElementChild?.querySelector('input')?.focus();
    });

    container.addEventListener('click', (e) => {
        const remove = e.target.closest('[data-remove-ingredient]');
        if (!remove) return;
        remove.closest('[data-ingredient-row]')?.remove();
    });
}

/* Avatar picker «+»: open the «Person hinzufügen» form */
function initAddPerson() {
    document.querySelectorAll('[data-open-person]').forEach((link) => {
        link.addEventListener('click', (e) => {
            const details = document.getElementById('new-person');
            if (!details) return;
            e.preventDefault();
            details.open = true;
            details.querySelector('input')?.focus();
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initConfirm();
    initToast();
    initShoppingToggle();
    initSwipe();
    initIngredientRows();
    initAddPerson();
});
