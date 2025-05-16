<?php

return [
    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of file uploads in the application.
    |
    */

    'max_upload_size' => env('LEAN_MAX_UPLOAD_SIZE', 10 * 1024 * 1024), // 10MB default

    'allowed_extensions' => [
        'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', // Images
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', // Documents
        'txt', 'csv', 'md', 'rtf', // Text files
        'zip', 'rar', 'tar', 'gz', // Archives
        'mp3', 'mp4', 'wav', 'avi', 'mov', 'webm', // Media
        'eot', 'ttf', 'woff', 'woff2', // Fonts
    ],

    /*
    |--------------------------------------------------------------------------
    | File Naming Strategy
    |--------------------------------------------------------------------------
    |
    | Configure how files should be named when uploaded
    |
    */
    'rename_files' => env('LEAN_FILESYSTEM_RENAME_FILES', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure caching for frequently accessed files
    |
    */
    'cache' => [
        'enabled' => env('LEAN_FILE_CACHE_ENABLED', true),
        'duration' => env('LEAN_FILE_CACHE_DURATION', 60), // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | S3 Specific Settings
    |--------------------------------------------------------------------------
    |
    | Additional settings for S3 storage
    |
    */
    's3' => [
        'url_expiration' => env('LEAN_S3_URL_EXPIRATION', 60), // minutes
        'retry_attempts' => env('LEAN_S3_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('LEAN_S3_RETRY_DELAY', 5), // seconds
    ],
];
