@extends('layouts.guest')

@section('title', 'Finko')

@section('content')
    <section class="relative overflow-hidden rounded-3xl border border-emerald-100 bg-gradient-to-br from-white via-emerald-50/40 to-white p-6 shadow-sm md:p-10">
        <div class="absolute -right-16 -top-16 h-44 w-44 rounded-full bg-emerald-100/50 blur-2xl" aria-hidden="true"></div>
        <div class="absolute -bottom-20 -left-12 h-56 w-56 rounded-full bg-teal-100/40 blur-2xl" aria-hidden="true"></div>

        <div class="relative grid gap-8 lg:grid-cols-2 lg:items-center">
            <div>
                <p class="mb-3 inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700">
                    Student Budget & Finance Tracker
                </p>
                <h1 class="text-4xl font-bold tracking-tight text-slate-900 md:text-5xl">Track every peso with less stress.</h1>
                <p class="mt-4 max-w-xl text-base leading-relaxed text-slate-600 md:text-lg">
                    Finko helps you manage income, spending, and budget goals in one simple flow so you can stay in control every week.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn-primary px-5 py-2.5">Create Free Account</a>
                    <a href="{{ route('login') }}" class="btn-secondary border-emerald-200 px-5 py-2.5 text-emerald-700 hover:bg-emerald-50">Login</a>
                </div>
                <div class="mt-5 flex flex-wrap gap-2 text-xs font-medium text-slate-600">
                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1">Category-based tracking</span>
                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1">Budget progress monitoring</span>
                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1">Soft delete + restore safety</span>
                </div>
            </div>

            <div class="rounded-2xl border border-emerald-100 bg-white/90 p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Monthly Snapshot</h2>
                <p class="mt-1 text-sm text-slate-500">Example overview inside your dashboard</p>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-3">
                        <dt class="text-slate-500">Income</dt>
                        <dd class="font-semibold text-emerald-700">PHP 12,500.00</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-3">
                        <dt class="text-slate-500">Expenses</dt>
                        <dd class="font-semibold text-amber-700">PHP 6,920.00</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-3">
                        <dt class="text-slate-500">Remaining Balance</dt>
                        <dd class="font-semibold text-emerald-700">PHP 5,580.00</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    <section class="mt-8 grid gap-4 md:grid-cols-3">
        <article class="app-card p-5">
            <h3 class="text-base font-semibold text-slate-900">Organize by Category</h3>
            <p class="mt-2 text-sm text-slate-600">Separate income and expense categories to keep your reports clean and readable.</p>
        </article>
        <article class="app-card p-5">
            <h3 class="text-base font-semibold text-slate-900">Control Budget Limits</h3>
            <p class="mt-2 text-sm text-slate-600">Set budget periods, monitor allocations, and quickly review active spending plans.</p>
        </article>
        <article class="app-card p-5">
            <h3 class="text-base font-semibold text-slate-900">Review Transactions Fast</h3>
            <p class="mt-2 text-sm text-slate-600">Search, filter, archive, and restore records without losing important finance history.</p>
        </article>
    </section>

    <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-7">
        <h2 class="text-xl font-semibold text-slate-900">How it works</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Step 1</p>
                <h3 class="mt-1 font-semibold text-slate-900">Set your categories</h3>
                <p class="mt-1 text-sm text-slate-600">Create clear income/expense categories for better tracking.</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Step 2</p>
                <h3 class="mt-1 font-semibold text-slate-900">Create budget plans</h3>
                <p class="mt-1 text-sm text-slate-600">Assign allocations, period dates, and monitor budget status.</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Step 3</p>
                <h3 class="mt-1 font-semibold text-slate-900">Log transactions daily</h3>
                <p class="mt-1 text-sm text-slate-600">Record expenses and income to keep your balance always updated.</p>
            </div>
        </div>
        <div class="mt-6 flex flex-wrap items-center gap-3">
            <a href="{{ route('register') }}" class="btn-primary">Start Tracking Now</a>
            <a href="{{ route('login') }}" class="btn-secondary">I Already Have an Account</a>
        </div>
    </section>
@endsection
