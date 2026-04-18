<?php

$recaptchaSiteKey = trim((string) env('RECAPTCHA_SITE_KEY', ''));
$recaptchaSecretKey = trim((string) env('RECAPTCHA_SECRET_KEY', ''));
$recaptchaKeysOk = $recaptchaSiteKey !== '' && $recaptchaSecretKey !== '';
$recaptchaEnabledRaw = env('RECAPTCHA_ENABLED');
$recaptchaLoginEnabled = $recaptchaKeysOk;
if ($recaptchaEnabledRaw !== null && $recaptchaEnabledRaw !== '') {
    $parsed = filter_var($recaptchaEnabledRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if ($parsed === false) {
        $recaptchaLoginEnabled = false;
    } elseif ($parsed === true) {
        $recaptchaLoginEnabled = $recaptchaKeysOk;
    }
}

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'recaptcha' => [
        'site_key' => $recaptchaSiteKey,
        'secret_key' => $recaptchaSecretKey,
        /** Login widget + server verify; false when keys missing or RECAPTCHA_ENABLED=false */
        'login_enabled' => $recaptchaLoginEnabled,
    ],

    'brevo' => [
        'api_key' => env('BREVO_API_KEY'),
    ],

    // Driver verification vendor (liveness / ID / face) keys
    'verification' => [
        'api_key' => env('VERIFICATION_API_KEY'),
    ],

    /*
    | DeepFace (Python-based face recognition) — deploy with Python and DeepFace.
    | Create a Face Recognition app and optional Face Verification app; set API keys below.
    | DEEPFACE_BASE_URL should be the server root, e.g. http://localhost:8000
    */
    'deepface' => [
        'base_url' => rtrim((string) env('DEEPFACE_BASE_URL', ''), '/'),
        'recognition_api_key' => env('DEEPFACE_RECOGNITION_API_KEY', ''),
        'verification_api_key' => env('DEEPFACE_VERIFICATION_API_KEY', ''),
        /** Minimum 1:1 similarity (0–1) for selfie vs ID photo auto-approval */
        'id_match_minimum' => (float) env('DEEPFACE_ID_MATCH_MINIMUM', 0.72),
    ],

];
