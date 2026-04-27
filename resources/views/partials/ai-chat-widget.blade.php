<div class="fixed bottom-6 right-6 z-[70]" data-ai-chat-widget>
    <!-- Toggle Button -->
    <button type="button"
        class="group inline-flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-900/20 transition-all duration-300 hover:scale-110 hover:shadow-xl hover:shadow-emerald-900/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300 focus-visible:ring-offset-2"
        aria-label="Open AI chat"
        aria-expanded="false"
        data-ai-chat-toggle>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transition-transform duration-300 group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 0 1-4.255-.949L3 20l1.114-3.342C3.41 15.456 3 13.784 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z" />
        </svg>
    </button>

    <!-- Chat Panel -->
    <div class="mt-3 hidden w-[min(92vw,420px)] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10 animate-in fade-in slide-in-from-bottom-4 duration-300"
        role="dialog"
        aria-label="Finko AI Assistant"
        aria-hidden="true"
        aria-modal="true"
        data-ai-chat-panel>

        <!-- Header -->
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-4 py-3.5">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse" aria-hidden="true"></div>
                    <p class="truncate text-sm font-bold text-slate-900">Finko AI</p>
                </div>
                <p class="mt-0.5 truncate text-xs text-slate-500" data-ai-chat-subtitle>Ask about budgets, transactions, and categories</p>
            </div>
            <div class="flex items-center gap-1.5 flex-shrink-0">
                <!-- Mode Selector -->
                <div class="hidden items-center rounded-lg border border-slate-200 bg-white p-0.5 text-xs font-semibold text-slate-700 sm:inline-flex" data-ai-chat-mode>
                    <button type="button"
                        class="rounded-md px-3 py-1.5 transition-all duration-200"
                        data-ai-chat-mode-btn="chatbot"
                        title="Read-only inquiry mode">
                        📖
                    </button>
                    <button type="button"
                        class="rounded-md px-3 py-1.5 transition-all duration-200"
                        data-ai-chat-mode-btn="assistant"
                        title="CRUD action mode">
                        ⚙️
                    </button>
                </div>
                <!-- Reset Button -->
                <button type="button"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300"
                    data-ai-chat-reset
                    title="Clear chat history">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
                <!-- Close Button -->
                <button type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300"
                    aria-label="Close AI chat"
                    data-ai-chat-close>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Error Alert -->
        <div class="hidden animate-in fade-in slide-in-from-top-2 border-b border-red-200 bg-red-50 px-4 py-2.5 text-xs font-medium text-red-800" data-ai-chat-error role="alert">
            <div class="flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span></span>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div class="hidden animate-in fade-in border-b border-emerald-100 bg-emerald-50 px-4 py-2.5 text-xs font-medium text-emerald-700" data-ai-chat-loading role="status" aria-live="polite">
            <div class="flex items-center gap-2">
                <div class="flex gap-1">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400 animate-bounce" style="animation-delay: 0ms;"></span>
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400 animate-bounce" style="animation-delay: 150ms;"></span>
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400 animate-bounce" style="animation-delay: 300ms;"></span>
                </div>
                <span>Thinking…</span>
            </div>
        </div>

        <!-- Messages Container -->
        <div class="flex flex-1 flex-col overflow-y-auto px-4 py-4 space-y-3 min-h-0" data-ai-chat-messages role="log" aria-live="polite" aria-label="Chat messages"></div>

        <!-- Empty State -->
        <div class="flex flex-col items-center justify-center px-4 py-8 text-center" data-ai-chat-empty>
            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <p class="text-sm font-semibold text-slate-900">Ask me anything!</p>
            <p class="mt-1 text-xs text-slate-600">Get insights about your budgets, transactions, and more.</p>
            <div class="mt-4 flex flex-col gap-2 w-full" data-ai-chat-suggestions>
                <!-- Suggestions will be added here -->
            </div>
        </div>

        <!-- Input Form -->
        <form class="flex items-center gap-2 border-t border-slate-100 bg-gradient-to-t from-white to-slate-50 px-4 py-3" data-ai-chat-form>
            <input type="text"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                placeholder="What would you like to know?"
                autocomplete="off"
                aria-label="Chat message input"
                data-ai-chat-input />
            <button type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition duration-200 hover:bg-emerald-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                data-ai-chat-send
                aria-label="Send message">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M16.6915026,12.4744748 L3.50612381,13.2599618 C3.19218622,13.2599618 3.03521743,13.4170592 3.03521743,13.5741566 L1.15159189,20.0151496 C0.8376543,20.8006365 0.99,21.89 1.77946707,22.52 C2.41,22.99 3.50612381,23.1 4.13399899,22.8429026 L21.714504,14.0454487 C22.6563168,13.5741566 23.1272231,12.6315722 22.9702544,11.6889879 L4.13399899,1.16207315 C3.34915502,0.9049757 2.40734225,1.01624348 1.77946707,1.4875356 C0.994623095,2.11926706 0.837654326,3.20860075 1.15159189,3.99408768 L3.03521743,10.4350806 C3.03521743,10.5921779 3.19218622,10.7492753 3.50612381,10.7492753 L16.6915026,11.5347622 C16.6915026,11.5347622 17.1624089,11.5347622 17.1624089,12.0060544 C17.1624089,12.4773465 16.6915026,12.4744748 16.6915026,12.4744748 Z" />
                </svg>
            </button>
        </form>
    </div>
</div>
