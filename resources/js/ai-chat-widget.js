const getCsrfToken = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    const token = meta?.getAttribute('content');
    return token || '';
};

const escapeHtml = (value) =>
    String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

const renderMessage = (container, message) => {
    const role = message?.role;
    const content = message?.content ?? '';

    if (!role || role === 'system') {
        return;
    }

    const wrapper = document.createElement('div');
    wrapper.className = 'space-y-2';
    const bubble = document.createElement('div');
    bubble.className =
        role === 'user'
            ? 'ml-auto max-w-[85%] whitespace-pre-wrap break-words rounded-2xl bg-emerald-600 px-3 py-2 text-sm leading-relaxed text-white'
            : 'mr-auto max-w-[85%] whitespace-pre-wrap break-words rounded-2xl bg-slate-100 px-3 py-2 text-sm leading-relaxed text-slate-800';
    bubble.textContent = String(content);

    wrapper.appendChild(bubble);
    container.appendChild(wrapper);
};

const renderHistory = (container, history) => {
    container.innerHTML = '';
    (Array.isArray(history) ? history : []).forEach((msg) => renderMessage(container, msg));
    container.scrollTop = container.scrollHeight;
};

const setVisible = (element, visible) => {
    element?.classList.toggle('hidden', !visible);
};

const setPanelOpen = (panel, open) => {
    panel.classList.toggle('hidden', !open);
    panel.setAttribute('aria-hidden', open ? 'false' : 'true');
};

const parseErrorMessage = async (response) => {
    try {
        const data = await response.json();
        if (data?.error) return String(data.error);
        const firstValidationError = data?.errors
            ? Object.values(data.errors).flat()?.[0]
            : null;
        if (firstValidationError) return String(firstValidationError);
    } catch {
        // ignore
    }

    if (response.status === 401) return 'Please log in again.';
    if (response.status === 422) return 'Please type a message.';
    if (response.status === 502) return 'AI service is unavailable. Please try again.';
    return 'Something went wrong. Please try again.';
};

export const setupAIChatWidget = () => {
    const root = document.querySelector('[data-ai-chat-widget]');
    if (!root) return;

    const toggle = root.querySelector('[data-ai-chat-toggle]');
    const panel = root.querySelector('[data-ai-chat-panel]');
    const close = root.querySelector('[data-ai-chat-close]');
    const reset = root.querySelector('[data-ai-chat-reset]');
    const form = root.querySelector('[data-ai-chat-form]');
    const input = root.querySelector('[data-ai-chat-input]');
    const send = root.querySelector('[data-ai-chat-send]');
    const messagesEl = root.querySelector('[data-ai-chat-messages]');
    const errorEl = root.querySelector('[data-ai-chat-error]');
    const loadingEl = root.querySelector('[data-ai-chat-loading]');

    if (!toggle || !panel || !form || !input || !messagesEl) return;

    let isOpen = false;
    let lastHistory = [];
    let pendingActionsContainer = null;

    const showError = (message) => {
        if (!errorEl) return;
        errorEl.textContent = message;
        setVisible(errorEl, true);
    };

    const clearError = () => setVisible(errorEl, false);
    const setLoading = (loading) => {
        setVisible(loadingEl, loading);
        input.disabled = loading;
        send && (send.disabled = loading);
    };

    const openPanel = () => {
        isOpen = true;
        setPanelOpen(panel, true);
        input.focus();
    };

    const closePanel = () => {
        isOpen = false;
        setPanelOpen(panel, false);
        clearError();
        setLoading(false);
    };

    toggle.addEventListener('click', () => (isOpen ? closePanel() : openPanel()));
    close?.addEventListener('click', closePanel);

    reset?.addEventListener('click', async () => {
        clearError();
        setLoading(true);
        try {
            const response = await fetch('/api/ai/chat/reset', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({}),
            });

            if (!response.ok) {
                showError(await parseErrorMessage(response));
                return;
            }

            const data = await response.json();
            lastHistory = Array.isArray(data?.history) ? data.history : [];
            renderHistory(messagesEl, lastHistory);
        } finally {
            setLoading(false);
        }
    });

    const clearPendingControls = () => {
        pendingActionsContainer?.remove();
        pendingActionsContainer = null;
    };

    const renderPendingControls = () => {
        clearPendingControls();

        const container = document.createElement('div');
        container.className = 'flex items-center justify-end gap-2';

        const confirmBtn = document.createElement('button');
        confirmBtn.type = 'button';
        confirmBtn.className =
            'rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60';
        confirmBtn.textContent = 'Confirm';

        const cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className =
            'rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60';
        cancelBtn.textContent = 'Cancel';

        container.appendChild(cancelBtn);
        container.appendChild(confirmBtn);
        messagesEl.appendChild(container);
        messagesEl.scrollTop = messagesEl.scrollHeight;
        pendingActionsContainer = container;

        const runPending = async (endpoint) => {
            clearError();
            setLoading(true);
            confirmBtn.disabled = true;
            cancelBtn.disabled = true;
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                    body: JSON.stringify({}),
                });

                if (!response.ok) {
                    showError(await parseErrorMessage(response));
                    return;
                }

                const data = await response.json();
                lastHistory = Array.isArray(data?.history) ? data.history : lastHistory;
                renderHistory(messagesEl, lastHistory);
                clearPendingControls();
            } finally {
                setLoading(false);
            }
        };

        confirmBtn.addEventListener('click', () => runPending('/api/ai/assistant/confirm'));
        cancelBtn.addEventListener('click', () => runPending('/api/ai/assistant/cancel'));
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearError();
        clearPendingControls();

        const message = input.value.trim();
        if (!message) {
            showError('Please type a message.');
            return;
        }

        input.value = '';
        lastHistory = [...lastHistory, { role: 'user', content: message }];
        renderHistory(messagesEl, lastHistory);
        setLoading(true);

        try {
            const response = await fetch('/api/ai/assistant', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ message }),
            });

            if (!response.ok) {
                showError(await parseErrorMessage(response));
                return;
            }

            const data = await response.json();
            lastHistory = Array.isArray(data?.history) ? data.history : lastHistory;
            if (!Array.isArray(data?.history) && data?.reply) {
                lastHistory = [...lastHistory, { role: 'assistant', content: String(data.reply) }];
            }

            renderHistory(messagesEl, lastHistory);

            if (data?.mode === 'propose') {
                renderPendingControls();
            }
        } finally {
            setLoading(false);
        }
    });
};
