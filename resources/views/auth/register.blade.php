@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass p-8">
        <h1 class="text-2xl font-bold text-white mb-6 text-center">Register</h1>
        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf
            <div>
                <label class="form-label">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="form-input">
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="form-input">
            </div>
            <div>
                <label class="form-label">Password</label>
                <input type="password" name="password" required class="form-input">
            </div>
            <div>
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" required class="form-input">
            </div>
            <button type="submit" class="btn-primary w-full">Create account</button>
        </form>
        <p class="text-sm mt-6 text-center text-slate-300">
            Already have an account? 
            <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 font-medium">Login</a>
        </p>
    </div>
</div>
@endsection

