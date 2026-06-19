<?php

return [

    /*
    |--------------------------------------------------------------------------
    | App Version Name
    |--------------------------------------------------------------------------
    |
    | Human-readable version of the app (e.g. "1.0.0").
    |
    */

    'version' => env('NATIVEPHP_APP_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | App Version Code
    |--------------------------------------------------------------------------
    |
    | Internal numeric version code for Play Store. Must increase per release.
    |
    */

    'version_code' => env('NATIVEPHP_APP_VERSION_CODE', 1),

    /*
    |--------------------------------------------------------------------------
    | App ID
    |--------------------------------------------------------------------------
    |
    | Unique package identifier for Android (reverse domain format).
    |
    */

    'app_id' => env('NATIVEPHP_APP_ID', 'com.qaterp.qat'),

    /*
    |--------------------------------------------------------------------------
    | Deeplink Scheme
    |--------------------------------------------------------------------------
    */

    'deeplink_scheme' => env('NATIVEPHP_DEEPLINK_SCHEME', 'qaterp'),

    /*
    |--------------------------------------------------------------------------
    | Deeplink Host
    |--------------------------------------------------------------------------
    */

    'deeplink_host' => env('NATIVEPHP_DEEPLINK_HOST'),

    /*
    |--------------------------------------------------------------------------
    | Start URL
    |--------------------------------------------------------------------------
    |
    | Initial URL when the app starts. Set to /login for auth-first apps.
    |
    */

    'start_url' => env('NATIVEPHP_START_URL', '/login'),

    /*
    |--------------------------------------------------------------------------
    | Development Team (iOS)
    |--------------------------------------------------------------------------
    */

    'development_team' => env('NATIVEPHP_DEVELOPMENT_TEAM'),

    /*
    |--------------------------------------------------------------------------
    | iOS Permission Strings
    |--------------------------------------------------------------------------
    */

    'permissions' => [
        'NSCameraUsageDescription' => 'يستخدم التطبيق الكاميرا لالتقاط الصور.',
        'NSMicrophoneUsageDescription' => 'يستخدم التطبيق الميكروفون لتسجيل الصوت.',
        'NSPhotoLibraryUsageDescription' => 'يستخدم التطبيق المعرض لاختيار الصور.',
    ],

    /*
    |--------------------------------------------------------------------------
    | iOS Permission String Localizations (Arabic)
    |--------------------------------------------------------------------------
    */

    'permission_localizations' => [
        'ar' => [
            'NSCameraUsageDescription' => 'يستخدم التطبيق الكاميرا لالتقاط الصور.',
            'NSMicrophoneUsageDescription' => 'يستخدم التطبيق الميكروفون لتسجيل الصوت.',
            'NSPhotoLibraryUsageDescription' => 'يستخدم التطبيق المعرض لاختيار الصور.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Keys to Clean Up Before Bundling
    |--------------------------------------------------------------------------
    */

    'cleanup_env_keys' => [
        'AWS_*',
        'GITHUB_*',
        'DO_SPACES_*',
        '*_SECRET',
        'DB_PASSWORD',
        'DB_USERNAME',
        'APP_KEY',
    ],

    /*
    |--------------------------------------------------------------------------
    | Files to Exclude Before Bundling
    |--------------------------------------------------------------------------
    */

    'cleanup_exclude_files' => [
        'storage/framework/sessions',
        'storage/framework/cache',
        'storage/framework/testing',
        'storage/logs/laravel.log',
    ],

    /*
    |--------------------------------------------------------------------------
    | Runtime Configuration
    |--------------------------------------------------------------------------
    |
    | 'persistent' mode: Laravel boots once, kernel reused (~5-30ms per request).
    | 'classic' mode: Full init/shutdown per request (~200-300ms).
    |
    */

    'runtime' => [
        'mode' => env('NATIVEPHP_RUNTIME_MODE', 'persistent'),
        'reset_instances' => true,
        'gc_between_dispatches' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Android Configuration
    |--------------------------------------------------------------------------
    */

    'android' => [
        'gradle_jdk_path' => env('NATIVEPHP_GRADLE_PATH'),
        'android_sdk_path' => env('NATIVEPHP_ANDROID_SDK_LOCATION'),
        'emulator_path' => env('ANDROID_EMULATOR'),
        '7zip-location' => env('NATIVEPHP_7ZIP_LOCATION', 'C:\\Program Files\\7-Zip\\7z.exe'),

        'status_bar_style' => env('NATIVEPHP_ANDROID_STATUS_BAR_STYLE', 'light'),

        'theme' => [
            'color_primary' => env('NATIVEPHP_ANDROID_COLOR_PRIMARY', '#1B5E20'),
            'color_primary_night' => env('NATIVEPHP_ANDROID_COLOR_PRIMARY_NIGHT', '#2E7D32'),
            'color_on_primary' => env('NATIVEPHP_ANDROID_COLOR_ON_PRIMARY', '#FFFFFF'),
        ],

        'build' => [
            'minify_enabled' => env('NATIVEPHP_ANDROID_MINIFY_ENABLED', false),
            'shrink_resources' => env('NATIVEPHP_ANDROID_SHRINK_RESOURCES', false),
            'obfuscate' => env('NATIVEPHP_ANDROID_OBFUSCATE', false),
            'debug_symbols' => env('NATIVEPHP_ANDROID_DEBUG_SYMBOLS', 'FULL'),
            'generate_mapping_files' => env('NATIVEPHP_ANDROID_MAPPING_FILES', false),
            'mapping_file_path' => env('NATIVEPHP_ANDROID_MAPPING_PATH', 'build/outputs/mapping/release/'),
            'keep_line_numbers' => env('NATIVEPHP_ANDROID_KEEP_LINE_NUMBERS', false),
            'keep_source_file' => env('NATIVEPHP_ANDROID_KEEP_SOURCE_FILE', false),
            'custom_proguard_rules' => env('NATIVEPHP_ANDROID_CUSTOM_PROGUARD_RULES', []),
            'parallel_builds' => env('NATIVEPHP_ANDROID_PARALLEL_BUILDS', true),
            'incremental_builds' => env('NATIVEPHP_ANDROID_INCREMENTAL_BUILDS', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Server Configuration
    |--------------------------------------------------------------------------
    */

    'server' => [
        'http_port' => env('NATIVEPHP_HTTP_PORT', 3000),
        'ws_port' => env('NATIVEPHP_WS_PORT', 8081),
        'service_name' => env('NATIVEPHP_SERVICE_NAME', 'Qat ERP Server'),
        'service_type' => '_http._tcp',
        'public_path' => env('NATIVEPHP_PUBLIC_PATH', 'public'),
        'build_path' => env('NATIVEPHP_BUILD_PATH', 'storage/app/native-build'),
        'open_browser' => env('NATIVEPHP_OPEN_BROWSER', false),
        'watch_paths' => [
            'app',
            'resources',
            'routes',
            'public/build',
        ],
        'watch_extensions' => ['php', 'blade.php', 'js', 'css', 'ts', 'vue', 'json'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Hot Reload Configuration
    |--------------------------------------------------------------------------
    */

    'hot_reload' => [
        'watch_paths' => [
            'app',
            'resources',
            'routes',
            'config',
            'public',
        ],
        'exclude_patterns' => [
            '\.git',
            'storage',
            'tests',
            'nativephp',
            'credentials',
            'node_modules',
            '\.swp',
            '\.tmp',
            '~',
            '\.log',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | App Store Connect API Configuration
    |--------------------------------------------------------------------------
    */

    'app_store_connect' => [
        'api_key' => env('APP_STORE_API_KEY'),
        'api_key_id' => env('APP_STORE_API_KEY_ID'),
        'api_issuer_id' => env('APP_STORE_API_ISSUER_ID'),
        'app_name' => env('APP_STORE_APP_NAME'),
    ],

    'ipad' => false,

    /*
    |--------------------------------------------------------------------------
    | Device Orientation Support
    |--------------------------------------------------------------------------
    |
    | Portrait only for mobile ERP app — no landscape needed.
    |
    */

    'orientation' => [
        'iphone' => [
            'portrait' => true,
            'upside_down' => false,
            'landscape_left' => false,
            'landscape_right' => false,
        ],
        'android' => [
            'portrait' => true,
            'upside_down' => false,
            'landscape_left' => false,
            'landscape_right' => false,
        ],
    ],
];
