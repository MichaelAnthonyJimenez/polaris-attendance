@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <section class="glass p-10">
        <div class="grid md:grid-cols-2 gap-8 items-center">
            <div>
                <p class="text-sm uppercase tracking-wide text-blue-200/80 mb-2">Polaris Multipurpose Cooperative</p>
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Modern attendance for taxi fleets.</h1>
                <p class="muted text-base leading-relaxed">
                    Capture check-ins with facial recognition, verify liveness, and sync offline sessions seamlessly
                    from kiosks or tablets—so admins see the right people at the right time.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    @guest
                        <a href="{{ route('register') }}" class="btn-primary">Get Started</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn-primary">Go to dashboard</a>
                        <a href="{{ route('drivers.index') }}" class="btn-secondary">Manage drivers</a>
                    @endguest
                </div>
            </div>
            <div class="glass p-6 border-white/10">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="text-sm text-slate-200/80">Live status</div>
                        <div class="text-2xl font-semibold text-white">Attendance overview</div>
                    </div>
                    <span class="px-3 py-1 text-xs rounded-full bg-emerald-400/20 text-emerald-200 border border-emerald-400/40">Online</span>
                </div>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="p-4 rounded-xl bg-white/5">
                        <div class="text-3xl font-bold text-white">24</div>
                        <div class="text-xs text-slate-300 mt-1">Drivers</div>
                    </div>
                    <div class="p-4 rounded-xl bg-white/5">
                        <div class="text-3xl font-bold text-white">18</div>
                        <div class="text-xs text-slate-300 mt-1">Check-ins today</div>
                    </div>
                    <div class="p-4 rounded-xl bg-white/5">
                        <div class="text-3xl font-bold text-white">4</div>
                        <div class="text-xs text-slate-300 mt-1">Pending sync</div>
                    </div>
                </div>
                <div class="mt-6 text-xs text-slate-300">Sample figures for illustration.</div>
            </div>
        </div>
    </section>

    <section class="grid md:grid-cols-3 gap-4">
        <div class="glass p-5 border-white/10">
            <h2 class="card-title mb-2">Capture & Sync</h2>
            <p class="muted text-sm">Record check-ins/outs with face evidence and liveness; sync offline events via device tokens.</p>
        </div>
        <div class="glass p-5 border-white/10">
            <h2 class="card-title mb-2">Driver Management</h2>
            <p class="muted text-sm">Enroll drivers, store badge and vehicle details, and manage active status with face templates.</p>
        </div>
        <div class="glass p-5 border-white/10">
            <h2 class="card-title mb-2">Insights</h2>
            <p class="muted text-sm">View dashboard summaries of daily check-ins/outs and recent activity at a glance.</p>
        </div>
    </section>
</div>
@endsection

