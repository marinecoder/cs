<?php
// Application Configuration
return [
    // Database Configuration
    'database' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'courier_app',
        'port' => 3306,
        'charset' => 'utf8mb4'
    ],

    // Application Settings
    'app' => [
        'name' => 'Courier Web App',
        'version' => '1.0.0',
        'debug' => false,
        'timezone' => 'UTC',
        'default_language' => 'en',
        'session_lifetime' => 7200, // 2 hours
        'upload_max_size' => 10485760, // 10MB
    ],

    // Security Configuration
    'security' => [
        'password_min_length' => 8,
        'session_name' => 'COURIER_SESSION',
        'csrf_protection' => true,
        'rate_limiting' => true,
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
    ],

    // Email Configuration
    'email' => [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_username' => '',
        'smtp_password' => '',
        'from_email' => 'noreply@courierapp.com',
        'from_name' => 'Courier App',
    ],

    // Notification Settings
    'notifications' => [
        'email_enabled' => true,
        'sms_enabled' => false,
        'push_enabled' => false,
        'tracking_updates' => true,
        'payment_confirmations' => true,
    ],

    // Payment Configuration
    'payment' => [
        'stripe_public_key' => '',
        'stripe_secret_key' => '',
        'paypal_client_id' => '',
        'paypal_client_secret' => '',
        'currency' => 'USD',
        'tax_rate' => 0.08, // 8%
    ],

    // Shipping Configuration
    'shipping' => [
        'default_rates' => [
            'standard' => 5.00,
            'express' => 8.00,
            'overnight' => 15.00
        ],
        'insurance_rate' => 2.00,
        'signature_fee' => 1.50,
        'max_weight' => 50.0, // kg
        'tracking_number_prefix' => 'CA',
    ],

    // File Upload Configuration
    'uploads' => [
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'max_file_size' => 5242880, // 5MB
        'upload_path' => '/uploads/',
        'image_quality' => 85,
    ],

    // API Configuration
    'api' => [
        'rate_limit' => 100, // requests per minute
        'cors_enabled' => true,
        'cors_origins' => ['*'],
        'api_key_required' => false,
        'version' => 'v1',
    ],

    // Caching Configuration
    'cache' => [
        'enabled' => true,
        'driver' => 'file', // file, redis, memcached
        'ttl' => 3600, // 1 hour
        'prefix' => 'courier_',
    ],

    // Logging Configuration
    'logging' => [
        'enabled' => true,
        'level' => 'ERROR', // DEBUG, INFO, WARNING, ERROR
        'file_path' => '/logs/app.log',
        'max_file_size' => 10485760, // 10MB
        'rotate_files' => true,
    ],

    // Backup Configuration
    'backup' => [
        'enabled' => true,
        'schedule' => 'daily',
        'retention_days' => 30,
        'backup_path' => '/backups/',
        'include_uploads' => true,
    ],

    // Third-party Services
    'services' => [
        'google_maps_api_key' => '',
        'mapbox_access_token' => '',
        'twilio_account_sid' => '',
        'twilio_auth_token' => '',
        'firebase_server_key' => '',
    ],

    // Features
    'features' => [
        'user_registration' => true,
        'email_verification' => true,
        'two_factor_auth' => false,
        'real_time_tracking' => true,
        'mobile_app_support' => true,
        'multi_language' => false,
        'dark_mode' => true,
    ],

    // Maintenance
    'maintenance' => [
        'enabled' => false,
        'message' => 'System is under maintenance. Please try again later.',
        'allowed_ips' => ['127.0.0.1'],
        'retry_after' => 3600, // 1 hour
    ],
];
