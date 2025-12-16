<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings (visible to all)
            ['key' => 'site_name', 'value' => 'Polaris Attendance', 'type' => 'string', 'group' => 'general', 'description' => 'Site name'],
            ['key' => 'site_description', 'value' => 'Taxi Driver Attendance Management System', 'type' => 'string', 'group' => 'general', 'description' => 'Site description'],
            ['key' => 'company_name', 'value' => 'Polaris Multipurpose Cooperative', 'type' => 'string', 'group' => 'general', 'description' => 'Company name'],
            ['key' => 'timezone', 'value' => 'UTC', 'type' => 'string', 'group' => 'general', 'description' => 'Default timezone'],
            ['key' => 'default_language', 'value' => 'en', 'type' => 'string', 'group' => 'general', 'description' => 'Default language'],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'type' => 'string', 'group' => 'general', 'description' => 'Default date format'],
            ['key' => 'time_format', 'value' => 'H:i', 'type' => 'string', 'group' => 'general', 'description' => 'Default time format'],
            ['key' => 'currency', 'value' => 'USD', 'type' => 'string', 'group' => 'general', 'description' => 'Default currency code'],
            ['key' => 'currency_symbol', 'value' => '$', 'type' => 'string', 'group' => 'general', 'description' => 'Currency symbol'],
            ['key' => 'country', 'value' => 'US', 'type' => 'string', 'group' => 'general', 'description' => 'Default country code'],
            ['key' => 'contact_email', 'value' => 'contact@polaris.test', 'type' => 'string', 'group' => 'general', 'description' => 'Contact email address'],
            ['key' => 'support_email', 'value' => 'support@polaris.test', 'type' => 'string', 'group' => 'general', 'description' => 'Support email address'],
            ['key' => 'phone_number', 'value' => '', 'type' => 'string', 'group' => 'general', 'description' => 'Contact phone number'],
            ['key' => 'address', 'value' => '', 'type' => 'string', 'group' => 'general', 'description' => 'Company address'],
            ['key' => 'welcome_message', 'value' => 'Welcome to Polaris Attendance System', 'type' => 'string', 'group' => 'general', 'description' => 'Welcome message displayed on login'],
            ['key' => 'footer_text', 'value' => '© 2024 Polaris Multipurpose Cooperative. All rights reserved.', 'type' => 'string', 'group' => 'general', 'description' => 'Footer text'],
            ['key' => 'enable_registration', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable user registration'],
            ['key' => 'enable_password_reset', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable password reset functionality'],
            ['key' => 'enable_email_verification', 'value' => '0', 'type' => 'boolean', 'group' => 'general', 'description' => 'Require email verification'],
            ['key' => 'enable_two_factor', 'value' => '0', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable two-factor authentication'],
            ['key' => 'session_lifetime', 'value' => '120', 'type' => 'integer', 'group' => 'general', 'description' => 'Session lifetime in minutes'],
            ['key' => 'remember_me_duration', 'value' => '20160', 'type' => 'integer', 'group' => 'general', 'description' => 'Remember me duration in minutes (14 days)'],
            ['key' => 'max_upload_size', 'value' => '10', 'type' => 'integer', 'group' => 'general', 'description' => 'Maximum file upload size (MB)'],
            ['key' => 'allowed_file_types', 'value' => 'jpg,jpeg,png,pdf,doc,docx', 'type' => 'string', 'group' => 'general', 'description' => 'Allowed file types (comma-separated)'],
            ['key' => 'enable_analytics', 'value' => '0', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable analytics tracking'],
            ['key' => 'enable_cookies', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable cookies'],
            ['key' => 'cookie_consent_required', 'value' => '0', 'type' => 'boolean', 'group' => 'general', 'description' => 'Require cookie consent'],
            ['key' => 'privacy_policy_url', 'value' => '', 'type' => 'string', 'group' => 'general', 'description' => 'Privacy policy URL'],
            ['key' => 'terms_of_service_url', 'value' => '', 'type' => 'string', 'group' => 'general', 'description' => 'Terms of service URL'],
            ['key' => 'enable_maintenance', 'value' => '0', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable maintenance mode'],
            ['key' => 'maintenance_message', 'value' => 'System is under maintenance. Please check back later.', 'type' => 'string', 'group' => 'general', 'description' => 'Maintenance mode message'],
            ['key' => 'enable_api_access', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable API access'],
            ['key' => 'enable_webhooks', 'value' => '0', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable webhook notifications'],
            ['key' => 'enable_logging', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable system logging'],
            ['key' => 'log_retention_days', 'value' => '30', 'type' => 'integer', 'group' => 'general', 'description' => 'Log retention period (days)'],
            ['key' => 'enable_backup', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable automatic backups'],
            ['key' => 'backup_frequency', 'value' => 'daily', 'type' => 'string', 'group' => 'general', 'description' => 'Backup frequency'],
            ['key' => 'enable_notifications', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable system notifications'],
            ['key' => 'notification_sound', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable notification sounds'],
            ['key' => 'enable_dark_mode', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable dark mode'],
            ['key' => 'default_theme', 'value' => 'dark', 'type' => 'string', 'group' => 'general', 'description' => 'Default theme'],
            ['key' => 'items_per_page', 'value' => '20', 'type' => 'integer', 'group' => 'general', 'description' => 'Default items per page'],
            ['key' => 'enable_search', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable search functionality'],
            ['key' => 'enable_filters', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable filter options'],
            ['key' => 'enable_export', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable data export'],
            ['key' => 'enable_import', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable data import'],
            ['key' => 'enable_bulk_actions', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable bulk actions'],
            ['key' => 'enable_sorting', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable column sorting'],
            ['key' => 'enable_pagination', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable pagination'],
            ['key' => 'enable_tooltips', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable tooltips'],
            ['key' => 'enable_animations', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable UI animations'],
            ['key' => 'enable_keyboard_shortcuts', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable keyboard shortcuts'],
            ['key' => 'enable_offline_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable offline mode'],
            ['key' => 'enable_sync', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable data synchronization'],
            ['key' => 'sync_interval', 'value' => '5', 'type' => 'integer', 'group' => 'general', 'description' => 'Sync interval in minutes'],
            ['key' => 'enable_cache', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable caching'],
            ['key' => 'cache_duration', 'value' => '60', 'type' => 'integer', 'group' => 'general', 'description' => 'Cache duration in minutes'],
            ['key' => 'enable_compression', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable data compression'],
            ['key' => 'enable_encryption', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable data encryption'],
            ['key' => 'enable_ssl', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Require SSL connection'],
            ['key' => 'enable_csrf_protection', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable CSRF protection'],
            ['key' => 'enable_xss_protection', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable XSS protection'],
            ['key' => 'enable_sql_injection_protection', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable SQL injection protection'],
            ['key' => 'enable_rate_limiting', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable rate limiting'],
            ['key' => 'rate_limit_per_minute', 'value' => '60', 'type' => 'integer', 'group' => 'general', 'description' => 'Rate limit per minute'],
            ['key' => 'enable_ip_whitelist', 'value' => '0', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable IP whitelist'],
            ['key' => 'enable_ip_blacklist', 'value' => '0', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable IP blacklist'],
            ['key' => 'enable_geo_blocking', 'value' => '0', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable geographic blocking'],
            ['key' => 'enable_device_tracking', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable device tracking'],
            ['key' => 'enable_location_tracking', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable location tracking'],
            ['key' => 'enable_activity_logging', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable activity logging'],
            ['key' => 'enable_audit_logging', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable audit logging'],
            ['key' => 'enable_error_logging', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable error logging'],
            ['key' => 'enable_performance_monitoring', 'value' => '0', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable performance monitoring'],
            ['key' => 'enable_debug_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable debug mode'],
            ['key' => 'enable_developer_tools', 'value' => '0', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable developer tools'],
            
            // Admin Settings - System Configuration
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'admin_system', 'description' => 'Enable maintenance mode'],
            ['key' => 'session_timeout', 'value' => '120', 'type' => 'integer', 'group' => 'admin_system', 'description' => 'Session timeout in minutes'],
            ['key' => 'max_login_attempts', 'value' => '5', 'type' => 'integer', 'group' => 'admin_system', 'description' => 'Maximum login attempts before lockout'],
            ['key' => 'password_min_length', 'value' => '8', 'type' => 'integer', 'group' => 'admin_system', 'description' => 'Minimum password length'],
            ['key' => 'require_password_change', 'value' => '0', 'type' => 'boolean', 'group' => 'admin_system', 'description' => 'Require password change on first login'],
            
            // Admin Settings - Attendance
            ['key' => 'face_recognition_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_attendance', 'description' => 'Enable facial recognition'],
            ['key' => 'liveness_detection_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_attendance', 'description' => 'Enable liveness detection'],
            ['key' => 'min_face_confidence', 'value' => '80', 'type' => 'integer', 'group' => 'admin_attendance', 'description' => 'Minimum face confidence percentage'],
            ['key' => 'min_liveness_score', 'value' => '0.7', 'type' => 'string', 'group' => 'admin_attendance', 'description' => 'Minimum liveness score'],
            ['key' => 'auto_checkout_hours', 'value' => '8', 'type' => 'integer', 'group' => 'admin_attendance', 'description' => 'Auto checkout after hours (0 to disable)'],
            ['key' => 'attendance_reminder_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_attendance', 'description' => 'Enable attendance reminders'],
            
            // Admin Settings - Face Recognition
            ['key' => 'face_recognition_provider', 'value' => 'default', 'type' => 'string', 'group' => 'admin_face_recognition', 'description' => 'Face recognition provider'],
            ['key' => 'max_face_images_per_driver', 'value' => '5', 'type' => 'integer', 'group' => 'admin_face_recognition', 'description' => 'Maximum face images per driver'],
            ['key' => 'face_matching_threshold', 'value' => '0.85', 'type' => 'string', 'group' => 'admin_face_recognition', 'description' => 'Face matching threshold (0-1)'],
            
            // Admin Settings - Notifications
            ['key' => 'email_notifications_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_notifications', 'description' => 'Enable email notifications'],
            ['key' => 'sms_notifications_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'admin_notifications', 'description' => 'Enable SMS notifications'],
            ['key' => 'notification_email', 'value' => 'admin@polaris.test', 'type' => 'string', 'group' => 'admin_notifications', 'description' => 'Admin notification email'],
            ['key' => 'notify_on_checkin', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_notifications', 'description' => 'Notify on driver check-in'],
            ['key' => 'notify_on_checkout', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_notifications', 'description' => 'Notify on driver check-out'],
            
            // Admin Settings - Security
            ['key' => 'two_factor_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'admin_security', 'description' => 'Enable two-factor authentication'],
            ['key' => 'ip_whitelist_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'admin_security', 'description' => 'Enable IP whitelist'],
            ['key' => 'audit_log_retention_days', 'value' => '90', 'type' => 'integer', 'group' => 'admin_security', 'description' => 'Audit log retention (days)'],
            ['key' => 'encrypt_sensitive_data', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_security', 'description' => 'Encrypt sensitive data'],
            
            // Admin Settings - Display
            ['key' => 'items_per_page', 'value' => '20', 'type' => 'integer', 'group' => 'admin_display', 'description' => 'Items per page in lists'],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'type' => 'string', 'group' => 'admin_display', 'description' => 'Date format'],
            ['key' => 'time_format', 'value' => 'H:i', 'type' => 'string', 'group' => 'admin_display', 'description' => 'Time format'],
            ['key' => 'theme', 'value' => 'dark', 'type' => 'string', 'group' => 'admin_display', 'description' => 'Theme (dark/light)'],
            
            // Driver Settings - Preferences
            ['key' => 'driver_theme', 'value' => 'dark', 'type' => 'string', 'group' => 'driver_preferences', 'description' => 'Preferred theme'],
            ['key' => 'driver_language', 'value' => 'en', 'type' => 'string', 'group' => 'driver_preferences', 'description' => 'Preferred language'],
            ['key' => 'driver_timezone', 'value' => 'UTC', 'type' => 'string', 'group' => 'driver_preferences', 'description' => 'Preferred timezone'],
            ['key' => 'show_notifications', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_preferences', 'description' => 'Show browser notifications'],
            
            // Driver Settings - Notifications
            ['key' => 'driver_email_notifications', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_notifications', 'description' => 'Receive email notifications'],
            ['key' => 'driver_sms_notifications', 'value' => '0', 'type' => 'boolean', 'group' => 'driver_notifications', 'description' => 'Receive SMS notifications'],
            ['key' => 'notify_checkin_reminder', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_notifications', 'description' => 'Notify me to check in'],
            ['key' => 'notify_checkout_reminder', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_notifications', 'description' => 'Notify me to check out'],
            ['key' => 'notification_sound', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_notifications', 'description' => 'Play notification sound'],
            
            // Driver Settings - Attendance
            ['key' => 'auto_capture_photo', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_attendance', 'description' => 'Auto capture photo on attendance'],
            ['key' => 'require_photo_attendance', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_attendance', 'description' => 'Require photo for attendance'],
            ['key' => 'show_attendance_history', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_attendance', 'description' => 'Show attendance history'],
            
            // Admin Settings - Backup & Data
            ['key' => 'auto_backup_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_backup', 'description' => 'Enable automatic backups'],
            ['key' => 'backup_frequency', 'value' => 'daily', 'type' => 'string', 'group' => 'admin_backup', 'description' => 'Backup frequency (daily/weekly/monthly)'],
            ['key' => 'backup_retention_days', 'value' => '30', 'type' => 'integer', 'group' => 'admin_backup', 'description' => 'Backup retention period (days)'],
            ['key' => 'backup_location', 'value' => 'local', 'type' => 'string', 'group' => 'admin_backup', 'description' => 'Backup storage location'],
            ['key' => 'backup_include_files', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_backup', 'description' => 'Include uploaded files in backup'],
            
            // Admin Settings - Reports
            ['key' => 'report_auto_generate', 'value' => '0', 'type' => 'boolean', 'group' => 'admin_reports', 'description' => 'Auto-generate daily reports'],
            ['key' => 'report_format', 'value' => 'pdf', 'type' => 'string', 'group' => 'admin_reports', 'description' => 'Default report format (pdf/excel/csv)'],
            ['key' => 'report_include_charts', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_reports', 'description' => 'Include charts in reports'],
            ['key' => 'report_email_recipients', 'value' => '', 'type' => 'string', 'group' => 'admin_reports', 'description' => 'Email addresses for report delivery (comma-separated)'],
            ['key' => 'report_retention_days', 'value' => '365', 'type' => 'integer', 'group' => 'admin_reports', 'description' => 'Report retention period (days)'],
            
            // Admin Settings - API & Integration
            ['key' => 'api_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_api', 'description' => 'Enable API access'],
            ['key' => 'api_rate_limit', 'value' => '100', 'type' => 'integer', 'group' => 'admin_api', 'description' => 'API rate limit per minute'],
            ['key' => 'api_key_expiry_days', 'value' => '90', 'type' => 'integer', 'group' => 'admin_api', 'description' => 'API key expiry (days, 0 for no expiry)'],
            ['key' => 'webhook_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'admin_api', 'description' => 'Enable webhook notifications'],
            ['key' => 'webhook_url', 'value' => '', 'type' => 'string', 'group' => 'admin_api', 'description' => 'Webhook URL for events'],
            
            // Admin Settings - Location & GPS
            ['key' => 'gps_tracking_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_location', 'description' => 'Enable GPS tracking'],
            ['key' => 'location_accuracy_required', 'value' => '50', 'type' => 'integer', 'group' => 'admin_location', 'description' => 'Required location accuracy (meters)'],
            ['key' => 'geofence_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'admin_location', 'description' => 'Enable geofencing'],
            ['key' => 'location_update_interval', 'value' => '5', 'type' => 'integer', 'group' => 'admin_location', 'description' => 'Location update interval (minutes)'],
            ['key' => 'require_location_checkin', 'value' => '0', 'type' => 'boolean', 'group' => 'admin_location', 'description' => 'Require location for check-in'],
            
            // Admin Settings - Driver Management
            ['key' => 'driver_registration_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_driver_management', 'description' => 'Allow driver self-registration'],
            ['key' => 'driver_approval_required', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_driver_management', 'description' => 'Require admin approval for new drivers'],
            ['key' => 'max_drivers_per_account', 'value' => '0', 'type' => 'integer', 'group' => 'admin_driver_management', 'description' => 'Maximum drivers (0 for unlimited)'],
            ['key' => 'driver_profile_completion_required', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_driver_management', 'description' => 'Require complete driver profile'],
            ['key' => 'driver_badge_required', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_driver_management', 'description' => 'Require badge number for drivers'],
            
            // Admin Settings - Data Export
            ['key' => 'export_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_export', 'description' => 'Enable data export'],
            ['key' => 'export_formats', 'value' => 'csv,excel,pdf', 'type' => 'string', 'group' => 'admin_export', 'description' => 'Available export formats'],
            ['key' => 'export_include_sensitive', 'value' => '0', 'type' => 'boolean', 'group' => 'admin_export', 'description' => 'Include sensitive data in exports'],
            ['key' => 'export_max_records', 'value' => '10000', 'type' => 'integer', 'group' => 'admin_export', 'description' => 'Maximum records per export'],
            
            // Admin Settings - Performance
            ['key' => 'cache_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_performance', 'description' => 'Enable caching'],
            ['key' => 'cache_ttl_minutes', 'value' => '60', 'type' => 'integer', 'group' => 'admin_performance', 'description' => 'Cache TTL (minutes)'],
            ['key' => 'query_optimization', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_performance', 'description' => 'Enable query optimization'],
            ['key' => 'image_compression', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_performance', 'description' => 'Enable image compression'],
            ['key' => 'max_upload_size_mb', 'value' => '10', 'type' => 'integer', 'group' => 'admin_performance', 'description' => 'Maximum upload size (MB)'],
            
            // Admin Settings - Compliance
            ['key' => 'gdpr_compliance', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_compliance', 'description' => 'Enable GDPR compliance features'],
            ['key' => 'data_retention_days', 'value' => '730', 'type' => 'integer', 'group' => 'admin_compliance', 'description' => 'Data retention period (days)'],
            ['key' => 'privacy_policy_url', 'value' => '', 'type' => 'string', 'group' => 'admin_compliance', 'description' => 'Privacy policy URL'],
            ['key' => 'terms_of_service_url', 'value' => '', 'type' => 'string', 'group' => 'admin_compliance', 'description' => 'Terms of service URL'],
            ['key' => 'require_privacy_consent', 'value' => '1', 'type' => 'boolean', 'group' => 'admin_compliance', 'description' => 'Require privacy consent'],
            
            // Admin Settings - Email
            ['key' => 'smtp_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'admin_email', 'description' => 'Use custom SMTP server'],
            ['key' => 'smtp_host', 'value' => '', 'type' => 'string', 'group' => 'admin_email', 'description' => 'SMTP host'],
            ['key' => 'smtp_port', 'value' => '587', 'type' => 'integer', 'group' => 'admin_email', 'description' => 'SMTP port'],
            ['key' => 'smtp_encryption', 'value' => 'tls', 'type' => 'string', 'group' => 'admin_email', 'description' => 'SMTP encryption (tls/ssl)'],
            ['key' => 'email_from_address', 'value' => 'noreply@polaris.test', 'type' => 'string', 'group' => 'admin_email', 'description' => 'Default from email address'],
            ['key' => 'email_from_name', 'value' => 'Polaris Attendance', 'type' => 'string', 'group' => 'admin_email', 'description' => 'Default from name'],
            
            // Driver Settings - Privacy
            ['key' => 'driver_share_location', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_privacy', 'description' => 'Share location with system'],
            ['key' => 'driver_share_photo', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_privacy', 'description' => 'Allow photo sharing'],
            ['key' => 'driver_profile_visible', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_privacy', 'description' => 'Make profile visible to admins'],
            ['key' => 'driver_data_export', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_privacy', 'description' => 'Allow data export'],
            ['key' => 'driver_analytics_opt_in', 'value' => '0', 'type' => 'boolean', 'group' => 'driver_privacy', 'description' => 'Opt-in to analytics'],
            
            // Driver Settings - Accessibility
            ['key' => 'driver_font_size', 'value' => 'medium', 'type' => 'string', 'group' => 'driver_accessibility', 'description' => 'Font size preference'],
            ['key' => 'driver_high_contrast', 'value' => '0', 'type' => 'boolean', 'group' => 'driver_accessibility', 'description' => 'Enable high contrast mode'],
            ['key' => 'driver_screen_reader', 'value' => '0', 'type' => 'boolean', 'group' => 'driver_accessibility', 'description' => 'Enable screen reader support'],
            ['key' => 'driver_animations', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_accessibility', 'description' => 'Enable animations'],
            ['key' => 'driver_keyboard_shortcuts', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_accessibility', 'description' => 'Enable keyboard shortcuts'],
            
            // Driver Settings - Dashboard
            ['key' => 'driver_dashboard_layout', 'value' => 'default', 'type' => 'string', 'group' => 'driver_dashboard', 'description' => 'Dashboard layout preference'],
            ['key' => 'driver_show_statistics', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_dashboard', 'description' => 'Show statistics on dashboard'],
            ['key' => 'driver_show_recent_activity', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_dashboard', 'description' => 'Show recent activity'],
            ['key' => 'driver_show_upcoming_events', 'value' => '0', 'type' => 'boolean', 'group' => 'driver_dashboard', 'description' => 'Show upcoming events'],
            ['key' => 'driver_refresh_interval', 'value' => '30', 'type' => 'integer', 'group' => 'driver_dashboard', 'description' => 'Auto-refresh interval (seconds)'],
            
            // Driver Settings - Data Usage
            ['key' => 'driver_data_saver_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'driver_data_usage', 'description' => 'Enable data saver mode'],
            ['key' => 'driver_auto_load_images', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_data_usage', 'description' => 'Auto-load images'],
            ['key' => 'driver_offline_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'driver_data_usage', 'description' => 'Enable offline mode'],
            ['key' => 'driver_sync_frequency', 'value' => '5', 'type' => 'integer', 'group' => 'driver_data_usage', 'description' => 'Data sync frequency (minutes)'],
            
            // Driver Settings - Profile
            ['key' => 'driver_show_badge_number', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_profile', 'description' => 'Show badge number on profile'],
            ['key' => 'driver_show_email', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_profile', 'description' => 'Show email on profile'],
            ['key' => 'driver_show_phone', 'value' => '0', 'type' => 'boolean', 'group' => 'driver_profile', 'description' => 'Show phone number on profile'],
            ['key' => 'driver_allow_profile_updates', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_profile', 'description' => 'Allow profile updates'],
            ['key' => 'driver_profile_photo_required', 'value' => '0', 'type' => 'boolean', 'group' => 'driver_profile', 'description' => 'Require profile photo'],
            
            // Driver Settings - Reminders
            ['key' => 'driver_checkin_reminder_time', 'value' => '09:00', 'type' => 'string', 'group' => 'driver_reminders', 'description' => 'Check-in reminder time'],
            ['key' => 'driver_checkout_reminder_time', 'value' => '17:00', 'type' => 'string', 'group' => 'driver_reminders', 'description' => 'Check-out reminder time'],
            ['key' => 'driver_reminder_before_minutes', 'value' => '15', 'type' => 'integer', 'group' => 'driver_reminders', 'description' => 'Reminder before event (minutes)'],
            ['key' => 'driver_reminder_repeat', 'value' => '1', 'type' => 'boolean', 'group' => 'driver_reminders', 'description' => 'Repeat reminders'],
            ['key' => 'driver_reminder_snooze', 'value' => '5', 'type' => 'integer', 'group' => 'driver_reminders', 'description' => 'Snooze duration (minutes)'],
            
            // Driver Settings - Security
            ['key' => 'driver_require_pin', 'value' => '0', 'type' => 'boolean', 'group' => 'driver_security', 'description' => 'Require PIN for attendance'],
            ['key' => 'driver_auto_lockout', 'value' => '0', 'type' => 'boolean', 'group' => 'driver_security', 'description' => 'Auto-lock after inactivity'],
            ['key' => 'driver_lockout_minutes', 'value' => '15', 'type' => 'integer', 'group' => 'driver_security', 'description' => 'Auto-lock timeout (minutes)'],
            ['key' => 'driver_biometric_auth', 'value' => '0', 'type' => 'boolean', 'group' => 'driver_security', 'description' => 'Enable biometric authentication'],
            ['key' => 'driver_session_timeout', 'value' => '60', 'type' => 'integer', 'group' => 'driver_security', 'description' => 'Session timeout (minutes)'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}

