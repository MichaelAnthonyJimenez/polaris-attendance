@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
    <div class="shell py-12">
        <div class="max-w-4xl mx-auto">
            <div class="mb-8">
                <a href="{{ route('home') }}" class="text-blue-400 hover:text-blue-300 transition flex items-center gap-2 mb-6">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Back
                </a>
                <h1 class="text-4xl font-bold text-white mb-2">Terms of Service</h1>
                <p class="text-slate-400">Last updated: {{ date('F j, Y') }}</p>
            </div>

            <div class="bg-slate-800/50 backdrop-blur-md rounded-xl border border-white/10 p-8 md:p-10 space-y-8 text-slate-300 leading-relaxed">
                <p class="text-slate-200">
                    These Terms of Service (“Terms”) govern your access to and use of {{ config('app.name', 'Polaris Attendance') }} and related services (collectively, the “Service”).
                    By creating an account, accessing the Service, or clicking to accept these Terms where prompted, you agree to be bound by these Terms.
                </p>

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold text-white">The Service</h2>
                    <p>
                        The Service provides tools for organizations to manage drivers, record attendance, and optionally verify identity using approved methods configured by the organization.
                        We may modify, suspend, or discontinue features with reasonable notice where practicable.
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold text-white">Accounts and eligibility</h2>
                    <p>
                        You must provide accurate registration information and keep your credentials confidential. You are responsible for activity under your account.
                        Administrators may invite users, assign roles, and configure features on behalf of the organization. If you do not agree to these Terms, do not use the Service.
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold text-white">Acceptable use</h2>
                    <p>You agree not to misuse the Service. For example, you must not:</p>
                    <ul class="list-disc pl-5 space-y-2">
                        <li>Attempt to gain unauthorized access to systems, data, or accounts.</li>
                        <li>Interfere with or disrupt the Service or servers or networks connected to the Service.</li>
                        <li>Use the Service to violate applicable law or to infringe others’ rights.</li>
                        <li>Upload malware or content intended to harm others or the Service.</li>
                    </ul>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold text-white">Organization data and verification</h2>
                    <p>
                        Your employer or cooperative may require identity verification, attendance capture, or other checks. Those requirements are set by the organization.
                        You authorize the processing of information as described in our
                        <a href="{{ route('privacy-policy') }}" class="text-blue-400 hover:text-blue-300">Privacy Policy</a>
                        and as configured by your administrator.
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold text-white">Disclaimers</h2>
                    <p>
                        The Service is provided “as is” and “as available” without warranties of any kind, whether express or implied, including implied warranties of merchantability,
                        fitness for a particular purpose, and non-infringement, to the fullest extent permitted by law.
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold text-white">Limitation of liability</h2>
                    <p>
                        To the fullest extent permitted by law, {{ config('app.name', 'Polaris Attendance') }} and its suppliers will not be liable for any indirect, incidental, special, consequential, or punitive damages,
                        or any loss of profits, data, or goodwill, arising from or related to your use of the Service.
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold text-white">Termination</h2>
                    <p>
                        We may suspend or terminate access to the Service for conduct that violates these Terms or creates risk or possible legal exposure.
                        You may stop using the Service at any time. Provisions that by their nature should survive termination shall survive.
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold text-white">Changes</h2>
                    <p>We may update these Terms from time to time. We will post the updated Terms on this page and update the “Last updated” date. Continued use after changes constitutes acceptance where permitted by law.</p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold text-white">Contact</h2>
                    <p>
                        For questions about these Terms, contact your organization’s administrator or use our
                        <a href="{{ route('contact') }}" class="text-blue-400 hover:text-blue-300">contact page</a>.
                    </p>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
