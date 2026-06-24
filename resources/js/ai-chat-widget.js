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

const formatTime = (date) => {
    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');
    return `${hours}:${minutes}`;
};

const parseMarkdownLike = (text) => {
    let result = text;
    // Bold: **text** -> <strong>text</strong>
    result = result.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
    // Italic: *text* -> <em>text</em> (but not **)
    result = result.replace(/(?<!\*)\*([^*]+)\*(?!\*)/g, '<em>$1</em>');
    return result;
};

const renderMessage = (container, message, index = 0) => {
    const role = message?.role;
    const content = message?.content ?? '';

    if (!role || role === 'system') {
        return;
    }

    const wrapper = document.createElement('div');
    wrapper.className = `flex ${role === 'user' ? 'justify-end' : 'justify-start'} animate-in fade-in slide-in-from-bottom-2 duration-300`;
    wrapper.setAttribute('data-message-index', index);

    const bubbleContainer = document.createElement('div');
    bubbleContainer.className = 'flex flex-col gap-1.5 max-w-[85%]';

    const bubble = document.createElement('div');
    bubble.className = role === 'user'
        ? 'rounded-2xl rounded-tr-none bg-gradient-to-br from-emerald-500 to-emerald-600 px-4 py-3 text-sm leading-relaxed text-white shadow-sm'
        : 'rounded-2xl rounded-tl-none bg-slate-100 px-4 py-3 text-sm leading-relaxed text-slate-800 shadow-sm';

    // Create content with proper formatting
    const contentDiv = document.createElement('div');
    contentDiv.className = 'whitespace-pre-wrap break-words prose prose-sm max-w-none';

    // Split content by line breaks
    const rawLines = String(content).split('\n');
    let processedLines = [];

    rawLines.forEach((line) => {
        if (line.trim() !== '') {
            processedLines.push(line);
        } else if (processedLines.length > 0 && processedLines[processedLines.length - 1] !== '') {
            // Keep single empty line for paragraph breaks
            processedLines.push('');
        }
    });

    let currentParagraph = [];
    processedLines.forEach((line, idx) => {
        if (line.trim() === '') {
            if (currentParagraph.length > 0) {
                const p = document.createElement('p');
                const htmlContent = parseMarkdownLike(currentParagraph.join('\n'));
                p.innerHTML = htmlContent;
                p.className = idx > 0 ? 'mt-2 mb-2' : 'mb-2';
                contentDiv.appendChild(p);
                currentParagraph = [];
            }
        } else {
            currentParagraph.push(line);
        }
    });

    // Add remaining paragraph
    if (currentParagraph.length > 0) {
        const p = document.createElement('p');
        const htmlContent = parseMarkdownLike(currentParagraph.join('\n'));
        p.innerHTML = htmlContent;
        contentDiv.appendChild(p);
    }

    // Fallback if no paragraphs created
    if (contentDiv.children.length === 0) {
        const p = document.createElement('p');
        p.innerHTML = parseMarkdownLike(String(content));
        contentDiv.appendChild(p);
    }

    bubble.appendChild(contentDiv);

    const timestamp = document.createElement('span');
    timestamp.className = `text-xs ${role === 'user' ? 'text-right text-emerald-600' : 'text-left text-slate-500'}`;
    timestamp.textContent = formatTime(new Date());

    bubbleContainer.appendChild(bubble);
    bubbleContainer.appendChild(timestamp);
    wrapper.appendChild(bubbleContainer);
    container.appendChild(wrapper);
};

const renderTypingIndicator = (container) => {
    const wrapper = document.createElement('div');
    wrapper.className = 'flex justify-start animate-in fade-in slide-in-from-bottom-2 duration-300';
    wrapper.setAttribute('data-typing-indicator', 'true');

    const bubble = document.createElement('div');
    bubble.className = 'rounded-2xl rounded-tl-none bg-slate-100 px-4 py-3 flex gap-1.5';

    for (let i = 0; i < 3; i++) {
        const dot = document.createElement('span');
        dot.className = 'inline-block h-2 w-2 rounded-full bg-slate-400 animate-bounce';
        dot.style.animationDelay = `${i * 150}ms`;
        bubble.appendChild(dot);
    }

    wrapper.appendChild(bubble);
    container.appendChild(wrapper);
    return wrapper;
};

const removeTypingIndicator = (container) => {
    const indicator = container.querySelector('[data-typing-indicator]');
    if (indicator) {
        indicator.remove();
    }
};

const scrollToBottom = (scrollContainer) => {
    if (!scrollContainer) return;
    scrollContainer.scrollTop = scrollContainer.scrollHeight;
};

const renderHistory = (container, history, scrollContainer = null) => {
    container.innerHTML = '';
    removeTypingIndicator(container);
    (Array.isArray(history) ? history : []).forEach((msg, idx) => renderMessage(container, msg, idx));
    scrollToBottom(scrollContainer || container);
};

const setVisible = (element, visible) => {
    element?.classList.toggle('hidden', !visible);
};

const setPanelOpen = (panel, open) => {
    panel.classList.toggle('hidden', !open);
    if (open) {
        panel.classList.add('animate-in', 'fade-in', 'slide-in-from-bottom-4', 'duration-300');
    }
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

    if (response.status === 401) return 'Your session expired. Please log in again.';
    if (response.status === 422) return 'Please type a message before sending.';
    if (response.status === 502) return 'AI service is temporarily unavailable. Please try again in a moment.';
    if (response.status === 429) return 'Too many requests. Please wait a moment before trying again.';
    if (response.status >= 500) return 'Server error. Please try again later.';
    return 'Something went wrong. Please try again or refresh the page.';
};

const shouldRefreshAfterResponse = (data) => Boolean(data?.created || data?.executed);

const DEFAULT_SUGGESTIONS = [
    { text: 'What budgets do I have?', icon: '💰' },
    { text: 'Show my latest transactions', icon: '📊' },
    { text: 'Total spending this month', icon: '💸' },
    { text: 'Top spending categories', icon: '🥇' },
];

const CONTEXTUAL_SUGGESTIONS = {
    chatbot: [
        { text: 'What categories do I have?', icon: '📁' },
        { text: 'How much did I spend this week?', icon: '📈' },
        { text: 'What\'s my largest expense?', icon: '💥' },
        { text: 'List my active budgets', icon: '✅' },
    ],
    assistant: [
        { text: 'Create a new budget', icon: '✏️' },
        { text: 'Add a new transaction', icon: '➕' },
        { text: 'Update a budget amount', icon: '🔄' },
        { text: 'Create a new category', icon: '🆕' },
    ],
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
    const scrollEl = root.querySelector('[data-ai-chat-scroll]');
    const errorEl = root.querySelector('[data-ai-chat-error]');
    const errorTextEl = errorEl?.querySelector('span');
    const loadingEl = root.querySelector('[data-ai-chat-loading]');
    const subtitleEl = root.querySelector('[data-ai-chat-subtitle]');
    const emptyStateEl = root.querySelector('[data-ai-chat-empty]');
    const suggestionsEl = root.querySelector('[data-ai-chat-suggestions]');
    const modeRoot = root.querySelector('[data-ai-chat-mode]');
    const modeButtons = root.querySelectorAll('[data-ai-chat-mode-btn]');

    if (!toggle || !panel || !form || !input || !messagesEl || !scrollEl) return;

    let isOpen = false;
    let lastHistory = [];
    let pendingActionsContainer = null;
    let mode = 'assistant';
    let isLoading = false;

    const MODE_KEY = 'finko-ai-mode';
    let lastModeNotice = null;

    const setEmptyStateVisible = (visible) => {
        setVisible(emptyStateEl, visible);
    };

    const renderModeNotice = (nextMode) => {
        if (!messagesEl || !scrollEl) return;

        // Hide empty state once user starts interacting with modes.
        setEmptyStateVisible(false);

        lastModeNotice?.remove();
        const notice = document.createElement('div');
        notice.className =
            'mx-auto my-2 w-fit max-w-[90%] rounded-full border border-slate-200 bg-white/90 px-3 py-1 text-[11px] font-semibold text-slate-600 shadow-sm';
        notice.textContent =
            nextMode === 'chatbot'
                ? 'Switched to Inquiry Mode (read-only)'
                : 'Switched to Assistant Mode (CRUD with confirmation)';

        messagesEl.appendChild(notice);
        lastModeNotice = notice;
        scrollToBottom(scrollEl);
    };

    // Force wheel/trackpad scrolling inside the widget to avoid the page stealing the scroll.
    // Some browser/layout combinations with nested flex + max-height can be finicky.
    scrollEl.addEventListener(
        'wheel',
        (e) => {
            if (scrollEl.scrollHeight <= scrollEl.clientHeight) return;

            const next = scrollEl.scrollTop + e.deltaY;
            const maxTop = Math.max(0, scrollEl.scrollHeight - scrollEl.clientHeight);
            scrollEl.scrollTop = Math.max(0, Math.min(maxTop, next));
            e.preventDefault();
        },
        { passive: false }
    );

    const getModeEndpoint = () => (mode === 'chatbot' ? '/api/ai/chat' : '/api/ai/assistant');
    const getHistoryEndpoint = () => '/api/ai/chat/history';
    const getResetEndpoint = () => '/api/ai/chat/reset';

    let didHydrateHistory = false;
    const hydrateHistory = async () => {
        if (didHydrateHistory) return;
        didHydrateHistory = true;

        try {
            const response = await fetch(getHistoryEndpoint(), {
                method: 'GET',
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                // Don't block opening the widget if history fails to load.
                return;
            }

            const data = await response.json();
            lastHistory = Array.isArray(data?.history) ? data.history : [];
            renderHistory(messagesEl, lastHistory, scrollEl);
            setEmptyStateVisible(lastHistory.length === 0);
            if (lastHistory.length === 0) {
                renderSuggestions();
            }
        } catch {
            // ignore
        }
    };

    const renderSuggestions = () => {
        if (!suggestionsEl) return;
        suggestionsEl.innerHTML = '';

        // Use contextual suggestions if there's no history, otherwise use default
        const suggestions = lastHistory.length === 0
            ? (CONTEXTUAL_SUGGESTIONS[mode] || DEFAULT_SUGGESTIONS)
            : DEFAULT_SUGGESTIONS;

        suggestions.forEach((suggestion) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'w-full rounded-md border border-emerald-200 bg-gradient-to-r from-emerald-50 to-emerald-50/50 px-2.5 py-1.5 text-left text-[11px] font-medium text-slate-700 transition hover:bg-emerald-100 hover:border-emerald-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300 active:scale-95';
            btn.innerHTML = `<span class="text-sm mr-1.5">${suggestion.icon}</span><span class="line-clamp-2 leading-tight">${escapeHtml(suggestion.text)}</span>`;
            btn.addEventListener('click', () => {
                input.value = suggestion.text;
                input.focus();
                form.dispatchEvent(new Event('submit'));
            });
            suggestionsEl.appendChild(btn);
        });
    };

    const applyModeUi = () => {
        if (subtitleEl) {
            subtitleEl.innerHTML = mode === 'chatbot'
                ? '📖 <strong>Inquiry Mode</strong> — Ask questions (read-only)'
                : '⚙️ <strong>Assistant Mode</strong> — Create, update, delete (with confirmation)';
        }

        modeButtons.forEach((btn) => {
            const btnMode = btn.getAttribute('data-ai-chat-mode-btn');
            const isActive = btnMode === mode;
            btn.classList.toggle('bg-emerald-600', isActive);
            btn.classList.toggle('text-white', isActive);
            btn.classList.toggle('shadow-sm', isActive);
            btn.classList.toggle('bg-white', !isActive);
            btn.classList.toggle('text-slate-700', !isActive);
            btn.classList.toggle('border', !isActive);
            btn.classList.toggle('border-slate-200', !isActive);
        });
    };

    const setMode = (next) => {
        const prev = mode;
        mode = next === 'chatbot' ? 'chatbot' : 'assistant';
        localStorage.setItem(MODE_KEY, mode);
        clearPendingControls();
        applyModeUi();
        if (prev !== mode) {
            renderModeNotice(mode);
        }
    };

    const showError = (message) => {
        if (!errorEl || !errorTextEl) return;
        const timestamp = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        errorTextEl.innerHTML = `<strong>Error:</strong> ${escapeHtml(message)}<br/><span class="text-xs opacity-75">at ${timestamp}</span>`;
        setVisible(errorEl, true);
        errorEl.setAttribute('role', 'alert');
        setTimeout(() => {
            if (errorEl) setVisible(errorEl, false);
        }, 8000);
    };

    const clearError = () => setVisible(errorEl, false);

    const setLoading = (loading) => {
        isLoading = loading;
        setVisible(loadingEl, loading);
        setEmptyStateVisible(!loading && lastHistory.length === 0);
        input.disabled = loading;
        send && (send.disabled = loading);
        if (loading) {
            renderTypingIndicator(messagesEl);
        } else {
            removeTypingIndicator(messagesEl);
        }
    };

    const openPanel = () => {
        isOpen = true;
        setPanelOpen(panel, true);
        toggle?.setAttribute('aria-expanded', 'true');
        setTimeout(() => input.focus(), 100);
        hydrateHistory();
    };

    const closePanel = () => {
        isOpen = false;
        setPanelOpen(panel, false);
        toggle?.setAttribute('aria-expanded', 'false');
        clearError();
        setLoading(false);
    };

    toggle.addEventListener('click', () => (isOpen ? closePanel() : openPanel()));
    close?.addEventListener('click', closePanel);

    // Keyboard support: Escape to close
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isOpen) {
            closePanel();
        }
    });

    modeButtons.forEach((btn) => {
        btn.addEventListener('click', () => setMode(btn.getAttribute('data-ai-chat-mode-btn')));
    });

    reset?.addEventListener('click', async () => {
        clearError();
        clearPendingControls();
        setLoading(true);
        try {
            const response = await fetch(getResetEndpoint(), {
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
            renderHistory(messagesEl, lastHistory, scrollEl);
            setEmptyStateVisible(lastHistory.length === 0);
            if (lastHistory.length === 0) {
                renderSuggestions();
            }
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
        container.className = 'flex items-center justify-end gap-2 px-2 py-3 rounded-lg bg-emerald-50 border border-emerald-200 animate-in fade-in slide-in-from-bottom-2 duration-300';

        const label = document.createElement('span');
        label.className = 'text-xs font-medium text-emerald-800';
        label.textContent = 'Confirm action?';

        const confirmBtn = document.createElement('button');
        confirmBtn.type = 'button';
        confirmBtn.className = 'rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60';
        confirmBtn.innerHTML = '✓ Confirm';

        const cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'rounded-lg border border-emerald-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60';
        cancelBtn.innerHTML = '✕ Cancel';

        container.appendChild(label);
        container.appendChild(cancelBtn);
        container.appendChild(confirmBtn);
        messagesEl.appendChild(container);
        setTimeout(() => {
            scrollToBottom(scrollEl);
        }, 0);
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
                renderHistory(messagesEl, lastHistory, scrollEl);
                clearPendingControls();

                if (shouldRefreshAfterResponse(data)) {
                    setTimeout(() => window.location.reload(), 1000);
                }
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
        setEmptyStateVisible(false);

        const message = input.value.trim();
        if (!message) {
            showError('Please type a message.');
            return;
        }

        input.value = '';
        input.style.minHeight = 'auto';
        lastHistory = [...lastHistory, { role: 'user', content: message }];
        renderHistory(messagesEl, lastHistory, scrollEl);
        setLoading(true);

        try {
            const response = await fetch(getModeEndpoint(), {
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
                setLoading(false);
                return;
            }

            const data = await response.json();
            lastHistory = Array.isArray(data?.history) ? data.history : lastHistory;
            if (!Array.isArray(data?.history) && data?.reply) {
                lastHistory = [...lastHistory, { role: 'assistant', content: String(data.reply) }];
            }

            removeTypingIndicator(messagesEl);
            renderHistory(messagesEl, lastHistory, scrollEl);

            if (mode === 'assistant' && data?.mode === 'propose') {
                renderPendingControls();
            }

            if (shouldRefreshAfterResponse(data)) {
                setTimeout(() => window.location.reload(), 1000);
            }
        } catch (error) {
            console.error('Chat error:', error);
            showError('Network error. Please try again.');
        } finally {
            setLoading(false);
        }
    });

    // Auto-focus and resize input
    input.addEventListener('input', () => {
        input.style.minHeight = 'auto';
        const scrollHeight = input.scrollHeight;
        input.style.minHeight = Math.min(scrollHeight, 100) + 'px';
    });

    const storedMode = localStorage.getItem(MODE_KEY);
    setMode(storedMode === 'chatbot' ? 'chatbot' : 'assistant');
    setVisible(modeRoot, true);
    setEmptyStateVisible(true);
    renderSuggestions();
};
