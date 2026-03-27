@extends('layouts.guest')

@section('title', 'Finko')

@section('content')
    <section class="grid gap-8 lg:grid-cols-2 lg:items-center">
        <div>
            <p class="mb-2 inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700">
                Student Finance Tracker
            </p>
            <h1 class="mb-4 text-4xl font-bold tracking-tight text-slate-900">Finko</h1>
            <p class="mb-3 text-lg text-slate-700">A student budget and finance tracker.</p>
            <p class="mb-6 text-sm leading-relaxed text-slate-600">
                Track your spending, organize categories, and manage your budget goals in one clean, focused dashboard.
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('login') }}" class="btn-primary px-5 py-2.5">
                    Login
                </a>
                <a href="{{ route('register') }}" class="btn-secondary border-emerald-200 px-5 py-2.5 text-emerald-700 hover:bg-emerald-50">
                    Sign Up
                </a>
            </div>
        </div>

        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Quick Snapshot</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex items-center justify-between rounded-lg bg-white px-4 py-3">
                    <dt class="text-slate-500">Monthly Budget</dt>
                    <dd class="font-semibold text-emerald-700">PHP 8,500</dd>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-white px-4 py-3">
                    <dt class="text-slate-500">Spent</dt>
                    <dd class="font-semibold text-slate-900">PHP 3,120</dd>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-white px-4 py-3">
                    <dt class="text-slate-500">Remaining</dt>
                    <dd class="font-semibold text-emerald-700">PHP 5,380</dd>
                </div>
            </dl>
        </div>
    </section>
@endsection
