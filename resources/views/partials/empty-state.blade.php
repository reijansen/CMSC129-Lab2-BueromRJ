@props([
    'title',
    'message',
    'actionLabel' => null,
    'actionHref' => null,
])

<div class="rounded-lg border border-emerald-200 bg-emerald-50 p-6 text-center">
    <h3 class="text-sm font-semibold text-emerald-900">{{ $title }}</h3>
    <p class="mt-1 text-sm text-emerald-800">{{ $message }}</p>
    @if ($actionLabel && $actionHref)
        <div class="mt-4">
            <a href="{{ $actionHref }}" class="btn-primary inline-flex">{{ $actionLabel }}</a>
        </div>
    @endif
</div>
