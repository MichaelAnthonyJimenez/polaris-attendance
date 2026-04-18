@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass p-8">
        <h1 class="text-2xl font-bold text-white mb-3 text-center">Forgot Password</h1>
        <p class="text-sm text-slate-300 text-center mb-6">
            Enter your email and we’ll send you a password reset link.
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

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf
            <div>
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="form-input" autofocus>
            </div>

            <button type="submit" class="btn-primary w-full">Send reset link</button>
        </form>

        <p class="text-sm mt-6 text-center text-slate-300">
            Remembered your password?
            <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 font-medium">Back to login</a>
        </p>
    </div>
</div>
@endsection
