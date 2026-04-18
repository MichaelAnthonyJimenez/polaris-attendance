@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass p-8 text-center">
        <h1 class="text-2xl font-bold text-white mb-3">Registration completed</h1>
        <p class="text-slate-300">
            Please proceed to login.
        </p>

        <div class="mt-6">
            <a href="{{ route('login') }}" class="btn-primary inline-flex items-center justify-center px-6">
                Login
            </a>
        </div>
    </div>
</div>
@endsection

