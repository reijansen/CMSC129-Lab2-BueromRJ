@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="section-title">Profile</h1>
            <p class="section-subtitle">Manage your account details.</p>
        </div>

        <section class="app-card max-w-2xl p-5">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Name</p>
                    <p class="text-sm font-medium text-slate-900">{{ $user->name }}</p>
                </div>
                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Email</p>
                    <p class="text-sm font-medium text-slate-900">{{ $user->email }}</p>
                </div>
                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Account Created</p>
                    <p class="text-sm font-medium text-slate-900">{{ $user->created_at?->format('M d, Y h:i A') ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Last Updated</p>
                    <p class="text-sm font-medium text-slate-900">{{ $user->updated_at?->format('M d, Y h:i A') ?? '-' }}</p>
                </div>
            </div>
        </section>
    </div>
@endsection
