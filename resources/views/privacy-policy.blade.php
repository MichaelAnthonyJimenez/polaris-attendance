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
                <h1 class="text-4xl font-bold text-white mb-2">Privacy Policy</h1>
                <p class="text-slate-400">Last updated: {{ date('F j, Y') }}</p>
            </div>

            <div class="bg-slate-800/50 backdrop-blur-md rounded-xl border border-white/10 p-8 md:p-10 space-y-8 text-slate-300 leading-relaxed">
                <p class="text-slate-200">
                    This Privacy Policy describes how {{ config('app.name', 'Polaris Attendance') }} (“we”, “us”, or “our”) collects, uses, and shares information
                    when you use our attendance and fleet-management services (the “Service”), including our web application and related features such as
                    driver verification and optional biometric attendance capture.
                </p>

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold text-white">Information we collect</h2>
                    <ul class="list-disc pl-5 space-y-2">
                        <li><span class="text-slate-200">Account and profile data:</span> name, email address, role, organization identifiers, and other details you or your administrator provide.</li>
                        <li><span class="text-slate-200">Operational data:</span> attendance events, timestamps, device or kiosk identifiers, location data where enabled by your organization, and audit logs relating to use of the Service.</li>
                        <li><span class="text-slate-200">Verification and biometric-related data:</span> images or templates used for identity verification and face-based attendance, where your organization has enabled those features and you have completed the relevant flow.</li>
                        <li><span class="text-slate-200">Technical data:</span> IP address, browser type, logs, and similar diagnostic information used to operate and secure the Service.</li>
                    </ul>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold text-white">How we use information</h2>
                    <p>We use the information above to provide, maintain, and improve the Service; authenticate users; process verification requests; generate reports for authorized administrators; detect abuse and protect security; and comply with applicable law. We do not sell your personal information.</p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold text-white">Sharing</h2>
                    <p>
                        We may share information with service providers who assist us in hosting, email delivery, or security, subject to appropriate safeguards.
                        Administrators within your organization may access driver and attendance records according to permissions configured for your deployment.
                        We may disclose information if required by law or to protect our rights and the safety of users.
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold text-white">Retention</h2>
                    <p>We retain information for as long as needed to provide the Service, satisfy legal obligations, resolve disputes, and enforce our agreements. Retention periods may be influenced by your organization’s configuration and applicable law.</p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold text-white">Security</h2>
                    <p>We implement reasonable technical and organizational measures designed to protect information against unauthorized access, alteration, or destruction. No method of transmission over the Internet is completely secure.</p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold text-white">Your choices and rights</h2>
                    <p>
                        Depending on your location, you may have rights to access, correct, delete, or restrict processing of your personal data, or to object to certain processing.
                        To exercise these rights, contact your organization’s administrator or reach out using the contact details in the Service.
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold text-white">Changes</h2>
                    <p>We may update this Privacy Policy from time to time. We will post the revised policy on this page and update the “Last updated” date above.</p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold text-white">Contact</h2>
                    <p>
                        Questions about this policy or our privacy practices may be directed to your organization’s support channel or
                        <a href="{{ route('contact') }}" class="text-blue-400 hover:text-blue-300">our contact page</a>.
                    </p>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
