@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass p-8">
        <h1 class="text-2xl font-bold text-white mb-3 text-center">Two-Factor Verification</h1>
        <p class="text-sm text-slate-300 text-center mb-6">
            We sent a 6-digit code to your email. Enter it below to confirm your email and continue signing in.
            <span class="block mt-2 text-slate-400">Driver accounts still require facial or ID verification and admin approval before full access.</span>
        </p>

        @if (session('status'))
            <div class="mb-4 text-sm text-emerald-300 bg-emerald-500/10 border border-emerald-500/20 rounded-lg px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-300 bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-3">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-5">
            @csrf
            <div>
                <label class="form-label">Verification code</label>
                <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code"
                       class="form-input tracking-widest text-center text-lg" placeholder="••••••" required autofocus>
            </div>
            <button type="submit" class="btn-primary w-full">Verify</button>
        </form>

        <form method="POST" action="{{ route('two-factor.resend') }}" class="mt-4">
            @csrf
            <button type="submit" class="btn-secondary w-full">Resend code</button>
        </form>

        <p class="text-sm mt-6 text-center text-slate-300">
            Want to try again?
            <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 font-medium">Back to login</a>
        </p>
    </div>
</div>
@endsection

