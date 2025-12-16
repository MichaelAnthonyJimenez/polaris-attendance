@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass p-8">
        <h1 class="text-2xl font-bold text-white mb-6 text-center">Login</h1>
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="form-input">
            </div>
            <div>
                <label class="form-label">Password</label>
                <input type="password" name="password" required class="form-input">
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded border-white/20 bg-white/5 text-blue-500 focus:ring-blue-500/50">
                <label for="remember" class="ml-2 text-sm text-slate-200">Remember me</label>
            </div>
            <button type="submit" class="btn-primary w-full">Login</button>
        </form>
        <p class="text-sm mt-6 text-center text-slate-300">
            Need an account? 
            <a href="{{ route('register') }}" class="text-blue-400 hover:text-blue-300 font-medium">Get Started</a>
        </p>
    </div>
</div>
@endsection

