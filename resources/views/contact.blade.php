@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
    <div class="shell py-12">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('home') }}" class="text-blue-400 hover:text-blue-300 transition flex items-center gap-2 mb-6">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Back
                </a>
                <h1 class="text-4xl font-bold text-white mb-2">Contact Us</h1>
                <p class="text-slate-400">Get in touch with our support team</p>
            </div>

            <!-- Content -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Contact Information -->
                <div class="bg-slate-800/50 backdrop-blur-md rounded-xl border border-white/10 p-8">
                    <h2 class="text-2xl font-semibold text-white mb-6">Contact Information</h2>

                    <div class="space-y-6">
                        <!-- Email -->
                        <div class="flex gap-4">
                            <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold mb-1">Email</h3>
                                <p class="text-slate-400">
                                    <a href="mailto:support@polaris.local" class="hover:text-blue-400 transition">support@polaris.local</a>
                                </p>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="flex gap-4">
                            <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold mb-1">Phone</h3>
                                <p class="text-slate-400">
                                    <a href="tel:+1234567890" class="hover:text-blue-400 transition">+1 (234) 567-890</a>
                                </p>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="flex gap-4">
                            <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold mb-1">Address</h3>
                                <p class="text-slate-400">
                                    {{ config('app.name', 'Polaris Attendance') }}<br/>
                                    123 Transportation Ave<br/>
                                    City, State 12345
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="text-white font-semibold mb-3">Location Map</h3>
                        <div class="rounded-xl overflow-hidden border border-white/10">
                            <iframe
                                src="https://www.google.com/maps?q=16.4189268,120.5894824&z=21&output=embed"
                                width="100%"
                                height="300"
                                style="border:0;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="bg-slate-800/50 backdrop-blur-md rounded-xl border border-white/10 p-8">
                    <h2 class="text-2xl font-semibold text-white mb-6">Send us a Message</h2>

                    <form class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Name</label>
                            <input type="text" placeholder="Your name" class="w-full px-4 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Email</label>
                            <input type="email" placeholder="your@email.com" class="w-full px-4 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Subject</label>
                            <input type="text" placeholder="Message subject" class="w-full px-4 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Message</label>
                            <textarea placeholder="Your message..." rows="4" class="w-full px-4 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition resize-none"></textarea>
                        </div>

                        <button type="submit" class="w-full px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

