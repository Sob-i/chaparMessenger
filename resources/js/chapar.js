/* =========================================================
   CHAPAR MESSENGER — UI KIT
   ========================================================= */

const Chapar = (() => {
    const $  = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

    /* ---------- Toast ---------- */
    function toast(message, type = 'info', duration = 3000) {
        const stack = $('#chapar-toast-stack');
        if (!stack) return;
        const el = document.createElement('div');
        el.className = `chapar-toast chapar-toast--${type}`;
        el.innerHTML = `
      <span class="chapar-toast__icon">●</span>
      <span class="chapar-toast__text"></span>
    `;
        el.querySelector('.chapar-toast__text').textContent = message;
        stack.appendChild(el);
        setTimeout(() => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(8px)';
            setTimeout(() => el.remove(), 220);
        }, duration);
    }

    /* ---------- Modal ---------- */
    function openModal({ title, body, actions = [] }) {
        closeModal();
        const backdrop = document.createElement('div');
        backdrop.className = 'chapar-modal-backdrop';
        backdrop.innerHTML = `
      <div class="chapar-modal">
        <div class="chapar-modal__header">
          <h3 class="chapar-modal__title"></h3>
          <button class="chapar-button chapar-button--ghost chapar-button--icon" data-close>✕</button>
        </div>
        <div class="chapar-modal__body"></div>
        <div class="chapar-modal__footer"></div>
      </div>
    `;
        backdrop.querySelector('.chapar-modal__title').textContent = title;
        const bodyEl = backdrop.querySelector('.chapar-modal__body');
        if (typeof body === 'string') bodyEl.innerHTML = body; else bodyEl.append(body);

        const footer = backdrop.querySelector('.chapar-modal__footer');
        actions.forEach(a => {
            const btn = document.createElement('button');
            btn.className = `chapar-button chapar-button--${a.variant || 'secondary'}`;
            btn.textContent = a.label;
            btn.addEventListener('click', () => a.onClick?.(closeModal));
            footer.appendChild(btn);
        });

        backdrop.querySelector('[data-close]').addEventListener('click', closeModal);
        backdrop.addEventListener('click', e => { if (e.target === backdrop) closeModal(); });
        document.body.appendChild(backdrop);
        return backdrop;
    }
    function closeModal() { $('.chapar-modal-backdrop')?.remove(); }

    /* ---------- Bottom Sheet ---------- */
    function openSheet({ title, content, actions = [] }) {
        closeSheet();
        const backdrop = document.createElement('div');
        backdrop.className = 'chapar-sheet-backdrop';
        backdrop.innerHTML = `
      <div class="chapar-sheet" role="dialog" aria-modal="true">
        <div class="chapar-sheet__grabber"></div>
        ${title ? `<h3 class="chapar-modal__title" style="margin:0 0 12px">${title}</h3>` : ''}
        <div class="chapar-sheet__body"></div>
        <div class="chapar-sheet__actions"></div>
      </div>
    `;
        const bodyEl = backdrop.querySelector('.chapar-sheet__body');
        if (typeof content === 'string') bodyEl.innerHTML = content; else bodyEl.append(content);

        const actionsEl = backdrop.querySelector('.chapar-sheet__actions');
        actions.forEach(a => {
            const btn = document.createElement('button');
            btn.className = `chapar-menu-item ${a.danger ? 'chapar-menu-item--danger' : ''}`;
            btn.innerHTML = `<span>${a.icon || ''}</span><span>${a.label}</span>`;
            btn.addEventListener('click', () => { a.onClick?.(); closeSheet(); });
            actionsEl.appendChild(btn);
        });

        backdrop.addEventListener('click', e => { if (e.target === backdrop) closeSheet(); });
        document.body.appendChild(backdrop);
        return backdrop;
    }
    function closeSheet() { $('.chapar-sheet-backdrop')?.remove(); }

    /* ---------- Context Menu / Dropdown ---------- */
    function openContextMenu({ x, y, items }) {
        closeContextMenu();
        const menu = document.createElement('div');
        menu.className = 'chapar-context-menu';
        items.forEach(it => {
            if (it.divider) {
                const d = document.createElement('div');
                d.className = 'chapar-menu-divider';
                menu.appendChild(d);
                return;
            }
            const b = document.createElement('button');
            b.className = `chapar-menu-item ${it.danger ? 'chapar-menu-item--danger' : ''}`;
            b.innerHTML = `<span>${it.icon || ''}</span><span>${it.label}</span>${it.shortcut ? `<span class="chapar-menu-item__shortcut">${it.shortcut}</span>` : ''}`;
            b.addEventListener('click', () => { it.onClick?.(); closeContextMenu(); });
            menu.appendChild(b);
        });
        document.body.appendChild(menu);
        const rect = menu.getBoundingClientRect();
        const vw = window.innerWidth, vh = window.innerHeight;
        menu.style.left = Math.min(x, vw - rect.width - 8) + 'px';
        menu.style.top  = Math.min(y, vh - rect.height - 8) + 'px';
        setTimeout(() => document.addEventListener('click', closeContextMenu, { once: true }), 0);
        return menu;
    }
    function closeContextMenu() { $('.chapar-context-menu')?.remove(); }

    /* ---------- Connection banner ---------- */
    function setConnection(state) {
        const el = $('#chapar-connection-banner');
        if (!el) return;
        const labels = {
            connecting:  'Connecting…',
            reconnecting:'Reconnecting…',
            offline:     'No connection',
            reconnected: 'Connection restored',
            connected:   '',
        };
        el.className = `chapar-connection-banner chapar-connection-banner--${state}`;
        el.textContent = labels[state] || '';
        el.hidden = state === 'connected';
        if (state === 'reconnected') setTimeout(() => setConnection('connected'), 2000);
    }

    /* ---------- Password toggle ---------- */
    document.addEventListener('click', e => {
        const btn = e.target.closest('[data-chapar-toggle-password]');
        if (!btn) return;
        const input = btn.closest('.chapar-input')?.querySelector('input');
        if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
    });

    return { toast, openModal, closeModal, openSheet, closeSheet,
        openContextMenu, closeContextMenu, setConnection };
})();

window.Chapar = Chapar;
