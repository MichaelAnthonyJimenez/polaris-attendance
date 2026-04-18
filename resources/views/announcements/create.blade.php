@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-white">Make an Announcement</h1>
            <p class="text-slate-400 mt-1">Send a message to your drivers via App, Email, or both.</p>
        </div>
        <a href="{{ route('announcements.index') }}" class="btn-secondary w-full sm:w-auto text-center">Back to Board</a>
    </div>

    <div class="glass p-6 sm:p-8">
        <form method="POST" action="{{ route('announcements.store') }}" class="space-y-5" id="announcementForm">
            @csrf

            <div>
                <label class="form-label">Title</label>
                <input
                    type="text"
                    name="title"
                    value="{{ old('title') }}"
                    required
                    maxlength="255"
                    class="form-input"
                    placeholder="Announcement title"
                />
                @error('title')
                    <p class="text-sm text-rose-300 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="form-label">Message</label>
                <textarea
                    name="body"
                    class="form-input"
                    rows="7"
                    required
                    maxlength="5000"
                    placeholder="Write the announcement message">{{ old('body') }}</textarea>
                @error('body')
                    <p class="text-sm text-rose-300 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="form-label">Delivery</label>
                <select
                    name="delivery_mode"
                    id="announcementDeliveryMode"
                    class="form-select"
                >
                    <option value="both" {{ old('delivery_mode', 'both') === 'both' ? 'selected' : '' }}>
                        App + Email
                    </option>
                    <option value="app" {{ old('delivery_mode') === 'app' ? 'selected' : '' }}>
                        App only
                    </option>
                    <option value="email" {{ old('delivery_mode') === 'email' ? 'selected' : '' }}>
                        Email only
                    </option>
                </select>
                <p class="text-xs text-slate-400 mt-1">
                    Drivers still control whether they receive announcements in-app/email from Settings.
                </p>
                @error('delivery_mode')
                    <p class="text-sm text-rose-300 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="form-label">Recipient</label>
                <select
                    name="send_to_all"
                    id="announcementRecipientMode"
                    class="form-select"
                >
                    <option value="1" {{ old('send_to_all', '1') == '1' ? 'selected' : '' }}>All drivers</option>
                    <option value="0" {{ old('send_to_all', '1') == '0' ? 'selected' : '' }}>Selected drivers</option>
                </select>
            </div>

            <div id="announcementRecipientPicker" class="{{ old('send_to_all', '1') == '0' ? '' : 'hidden' }}">
                <label class="form-label">Select drivers</label>
                <div class="form-input min-h-[14rem] max-h-64 overflow-y-auto space-y-2">
                    @php
                        $selectedDriverIds = array_map('strval', old('selected_user_ids', []));
                    @endphp
                    @foreach($drivers as $driver)
                        <label class="flex items-center gap-2 text-sm text-slate-200">
                            <input
                                type="checkbox"
                                name="selected_user_ids[]"
                                value="{{ $driver->id }}"
                                class="w-4 h-4 rounded border-white/20 bg-white/5 text-blue-500 focus:ring-blue-500/50"
                                {{ in_array((string) $driver->id, $selectedDriverIds, true) ? 'checked' : '' }}
                            >
                            <span>{{ $driver->name }} ({{ $driver->badge_number }})</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-slate-400 mt-1">Select one or more drivers.</p>
                @error('selected_user_ids')
                    <p class="text-sm text-rose-300 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="form-label">Expires at (optional)</label>
                <input
                    type="datetime-local"
                    name="expires_at"
                    value="{{ old('expires_at') }}"
                    class="form-input"
                />
                @error('expires_at')
                    <p class="text-sm text-rose-300 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('announcements.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Publish Announcement</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        const modeEl = document.getElementById('announcementRecipientMode');
        const pickerEl = document.getElementById('announcementRecipientPicker');
        if (!modeEl || !pickerEl) return;

        function sync() {
            const mode = String(modeEl.value ?? '1');
            pickerEl.classList.toggle('hidden', mode !== '0');
        }

        modeEl.addEventListener('change', sync);
        sync();
    })();
</script>
@endpush
@endsection

