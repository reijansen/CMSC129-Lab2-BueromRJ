<div class="fixed bottom-6 right-6 z-[70]" data-ai-chat-widget>
    <button type="button"
        class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-900/10 transition hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300"
        aria-label="Open AI chat"
        data-ai-chat-toggle>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 0 1-4.255-.949L3 20l1.114-3.342C3.41 15.456 3 13.784 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z" />
        </svg>
    </button>

    <div class="mt-3 hidden w-[min(92vw,420px)] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
        role="dialog"
        aria-label="AI Chat"
        aria-hidden="true"
        data-ai-chat-panel>
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-slate-900">Finko AI</p>
                <p class="truncate text-xs text-slate-500" data-ai-chat-subtitle>Ask about budgets, transactions, and categories</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="hidden items-center rounded-xl border border-slate-200 bg-white p-1 text-xs font-semibold text-slate-700 sm:flex" data-ai-chat-mode>
                    <button type="button"
                        class="rounded-lg px-2.5 py-1.5 transition"
                        data-ai-chat-mode-btn="chatbot">
                        Chatbot
                    </button>
                    <button type="button"
                        class="rounded-lg px-2.5 py-1.5 transition"
                        data-ai-chat-mode-btn="assistant">
                        Assistant
                    </button>
                </div>
                <button type="button"
                    class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300"
                    data-ai-chat-reset>
                    Reset
                </button>
                <button type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300"
                    aria-label="Close AI chat"
                    data-ai-chat-close>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="hidden border-b border-slate-200 bg-red-50 px-4 py-2 text-xs text-red-800" data-ai-chat-error></div>
        <div class="hidden border-b border-slate-200 bg-slate-50 px-4 py-2 text-xs text-slate-700" data-ai-chat-loading>Thinking…</div>

        <div class="h-80 space-y-3 overflow-y-auto px-4 py-4 text-sm" data-ai-chat-messages></div>

        <form class="flex items-center gap-2 border-t border-slate-200 px-4 py-3" data-ai-chat-form>
            <input type="text"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                placeholder="Type a question…"
                autocomplete="off"
                data-ai-chat-input />
            <button type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                data-ai-chat-send>
                Send
            </button>
        </form>
    </div>
</div>
