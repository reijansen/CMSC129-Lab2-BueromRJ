@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="space-y-5">
        <section class="relative overflow-hidden rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-teal-50 p-5">
            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    @if ($user->avatar_path)
                        <img src="{{ asset('storage/'.$user->avatar_path) }}" alt="Profile picture"
                            class="h-14 w-14 rounded-2xl border border-emerald-200 object-cover shadow-sm">
                    @else
                        <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-600 text-xl font-semibold text-white shadow-sm">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900">Profile</h1>
                        <p class="mt-1 text-sm text-slate-600">Update your details and profile picture.</p>
                    </div>
                </div>
                <span class="inline-flex w-fit rounded-full border border-emerald-200 bg-white/70 px-3 py-1 text-xs font-medium text-emerald-700">
                    Account active
                </span>
            </div>
        </section>

        <section class="app-card w-full p-0">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Account Details</h2>
                <p class="mt-1 text-sm text-slate-500">Email is locked. Other fields are editable.</p>
            </div>

            <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data"
                class="space-y-5 px-5 py-5" data-profile-form>
                @csrf
                @method('put')

                <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="space-y-4">
                        <label for="name" class="label-control">Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                            class="input-control" data-dirty-track>

                        <label for="username" class="label-control">Username</label>
                        <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}"
                            class="input-control" placeholder="username" data-dirty-track>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="phone" class="label-control">Phone</label>
                                <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}"
                                    class="input-control" placeholder="+63..." data-dirty-track>
                            </div>
                            <div>
                                <label for="birth_date" class="label-control">Birth Date</label>
                                <input id="birth_date" name="birth_date" type="date"
                                    value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}"
                                    class="input-control" data-dirty-track>
                            </div>
                        </div>

                        <label for="location" class="label-control">Location</label>
                        <input id="location" name="location" type="text" value="{{ old('location', $user->location) }}"
                            class="input-control" placeholder="City, Country" data-dirty-track>

                        <label for="bio" class="label-control">Bio</label>
                        <textarea id="bio" name="bio" rows="4" class="input-control" placeholder="Tell us about yourself..."
                            data-dirty-track>{{ old('bio', $user->bio) }}</textarea>
                    </div>

                    <aside class="space-y-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm font-semibold text-slate-900">Profile Picture</p>
                        <div class="mx-auto h-32 w-32 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                            @if ($user->avatar_path)
                                <img src="{{ asset('storage/'.$user->avatar_path) }}" alt="Profile picture preview"
                                    class="h-full w-full object-cover" data-avatar-preview>
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-emerald-100 text-3xl font-semibold text-emerald-700"
                                    data-avatar-preview-fallback>
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <img src="" alt="Profile picture preview" class="hidden h-full w-full object-cover"
                                    data-avatar-preview>
                            @endif
                        </div>

                        <div>
                            <label for="avatar" class="label-control">Upload New Image</label>
                            <input id="avatar" name="avatar" type="file" accept="image/*" class="sr-only"
                                data-dirty-track data-avatar-input>
                            <div class="flex items-center gap-3">
                                <button type="button" class="btn-secondary" data-avatar-trigger>Choose Image</button>
                                <span class="text-sm text-slate-500" data-avatar-filename>No file chosen</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">PNG/JPG up to 2MB.</p>
                        </div>

                        <div class="space-y-3 border-t border-slate-200 pt-3">
                            <div>
                                <label for="email" class="label-control">Email</label>
                                <input id="email" type="email" value="{{ $user->email }}" readonly
                                    class="input-control cursor-not-allowed bg-slate-100 text-slate-500">
                            </div>
                            <div class="grid gap-3">
                                <div>
                                    <label for="created_at" class="label-control">Account Created</label>
                                    <input id="created_at" type="text" value="{{ $user->created_at?->format('M d, Y h:i A') ?? '-' }}" readonly
                                        class="input-control cursor-not-allowed bg-slate-100 text-slate-500">
                                </div>
                                <div>
                                    <label for="updated_at" class="label-control">Last Updated</label>
                                    <input id="updated_at" type="text" value="{{ $user->updated_at?->format('M d, Y h:i A') ?? '-' }}" readonly
                                        class="input-control cursor-not-allowed bg-slate-100 text-slate-500">
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                    <button type="submit" class="btn-primary opacity-50" data-save-button disabled>Save Changes</button>
                </div>
            </form>
        </section>
    </div>

    <script>
        (() => {
            const form = document.querySelector('[data-profile-form]');
            if (!form) return;

            const saveButton = form.querySelector('[data-save-button]');
            const tracked = Array.from(form.querySelectorAll('[data-dirty-track]'));
            const avatarInput = form.querySelector('[data-avatar-input]');
            const avatarTrigger = form.querySelector('[data-avatar-trigger]');
            const avatarFilename = form.querySelector('[data-avatar-filename]');
            const avatarPreview = form.querySelector('[data-avatar-preview]');
            const avatarFallback = form.querySelector('[data-avatar-preview-fallback]');

            const initialValues = new Map();
            tracked.forEach((el) => {
                initialValues.set(el.name, el.type === 'file' ? '' : (el.value ?? ''));
            });

            const setDirtyState = () => {
                const hasChanges = tracked.some((el) => {
                    if (el.type === 'file') {
                        return (el.files?.length ?? 0) > 0;
                    }

                    return (el.value ?? '') !== (initialValues.get(el.name) ?? '');
                });

                saveButton.disabled = !hasChanges;
                saveButton.classList.toggle('opacity-50', !hasChanges);
                saveButton.classList.toggle('cursor-not-allowed', !hasChanges);
            };

            tracked.forEach((el) => {
                const evt = el.tagName === 'SELECT' ? 'change' : 'input';
                el.addEventListener(evt, setDirtyState);
                el.addEventListener('change', setDirtyState);
            });

            avatarTrigger?.addEventListener('click', () => {
                avatarInput?.click();
            });

            avatarInput?.addEventListener('change', () => {
                const file = avatarInput.files?.[0];
                if (avatarFilename) {
                    avatarFilename.textContent = file ? file.name : 'No file chosen';
                }

                if (!file || !avatarPreview) return;

                const reader = new FileReader();
                reader.onload = (event) => {
                    avatarPreview.src = event.target?.result?.toString() || '';
                    avatarPreview.classList.remove('hidden');
                    avatarFallback?.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            });

            setDirtyState();
        })();
    </script>
@endsection
