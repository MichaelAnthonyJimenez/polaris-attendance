 <?php

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

    'brevo' => [
        // Support both common variable names used in hosts.
        'api_key' => env('BREVO_API_KEY', env('BREVO_KEY')),
    ],

    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
        'login_enabled' => env('TURNSTILE_LOGIN_ENABLED', true),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', rtrim((string) env('APP_URL', ''), '/') . '/auth/google/callback'),
    ],

    'ocr_space' => [
        // Supports both OCR_SPACE_API_KEY and OCR_API_KEY.
        // Falls back to OCR.Space demo key to avoid "skipped" in local/dev.
        'api_key' => env('OCR_SPACE_API_KEY', env('OCR_API_KEY', 'helloworld')),
        'endpoint' => env('OCR_SPACE_ENDPOINT', 'https://api.ocr.space/parse/image'),
        'language' => env('OCR_SPACE_LANGUAGE', 'eng'),
        // OCR provider: ocr_space, easyocr, paddleocr
        'provider' => env('OCR_PROVIDER', 'ocr_space'),
        'python_bin' => env('OCR_PYTHON_BIN', 'python'),
        'script_path' => env('OCR_SCRIPT_PATH', base_path('scripts/ocr_local.py')),
        'local_language' => env('OCR_LOCAL_LANGUAGE', 'en'),
    ],

];
