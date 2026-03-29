@if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm" data-alert>
        <div class="flex items-start justify-between gap-3">
            <p>{{ session('success') }}</p>
            <button type="button"
                class="-mr-1 inline-flex h-6 w-6 items-center justify-center rounded-md text-emerald-700 transition hover:bg-emerald-100"
                data-close-alert aria-label="Dismiss alert">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm" data-alert>
        <div class="flex items-start justify-between gap-3">
            <p>{{ session('error') }}</p>
            <button type="button"
                class="-mr-1 inline-flex h-6 w-6 items-center justify-center rounded-md text-red-700 transition hover:bg-red-100"
                data-close-alert aria-label="Dismiss alert">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm" data-alert>
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="mb-2 font-semibold">Please review the following:</p>
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button"
                class="-mr-1 inline-flex h-6 w-6 items-center justify-center rounded-md text-red-700 transition hover:bg-red-100"
                data-close-alert aria-label="Dismiss alert">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
@endif

<script>
    (() => {
        document.querySelectorAll('[data-close-alert]').forEach((button) => {
            button.addEventListener('click', () => {
                button.closest('[data-alert]')?.remove();
            });
        });
    })();
</script>
