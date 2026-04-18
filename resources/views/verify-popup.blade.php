<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Verification Required</title>
    @include('components.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-3xl p-6 space-y-4">
        @if (session('error'))
            <div class="rounded-lg border border-red-500/40 bg-red-500/10 px-4 py-3 text-red-200">
                {{ session('error') }}
            </div>
        @endif
        @if (session('status'))
            <div class="rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-emerald-200">
                {{ session('status') }}
            </div>
        @endif
        <div class="glass p-6 rounded-xl border border-white/10">
            <h1 class="text-2xl font-bold text-white mb-2">Verification required</h1>
            <p class="text-sm text-slate-300 mb-5">
                Before you can access the system, please verify using one of the options below.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-xl border border-white/10 bg-white/5 p-5">
                    <h2 class="text-lg font-semibold text-white mb-1">Live detection (facial)</h2>
                    <p class="text-sm text-slate-300 mb-4">Use your camera to complete a quick liveness + face verification.</p>
                    <a href="{{ route('verification.facial') }}" class="btn-primary inline-flex">Start</a>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-5">
                    <h2 class="text-lg font-semibold text-white mb-1">ID verification</h2>
                    <p class="text-sm text-slate-300 mb-4">Capture the front and back of your ID card.</p>
                    <a href="{{ route('verification.id') }}" class="btn-secondary inline-flex">Continue</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
