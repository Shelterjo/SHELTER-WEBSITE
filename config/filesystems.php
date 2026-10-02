<?php

$appUrl = env('APP_URL', 'http://localhost');
$appUrl = is_string($appUrl) ? rtrim($appUrl, '/') : 'http://localhost';

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => false, // No public file-serving routes for private storage (SECURITY-CENTER.md)
            'throw' => false,
            'report' => false,
        ],

        // An empty variable in .env (VAR=) must fall back to the safe default: env() returns '' for it, and a local disk
        // with an empty root writes next to the running script — inside public/ under a web server (FINAL-QA QA-001).
        // Media library originals (MEDIA-RIGHTS): private, never served — only approved web copies are published.
        'media' => [
            'driver' => 'local',
            'root' => env('MEDIA_STORAGE_ROOT') ?: storage_path('app/private/media'),
            'visibility' => 'private',
            'directory_visibility' => 'private',
        ],

        // Web copies of APPROVED assets only (MEDIA-011): resized WebP/AVIF, content-hashed names, served as static files.
        'media_public' => [
            'driver' => 'local',
            'root' => env('MEDIA_PUBLIC_ROOT') ?: public_path('media'),
            'url' => '/media',
            'visibility' => 'public',
        ],

        // Private careers files (CAREERS-037, INFRA-038): outside the public web root, never served by URL, no execute
        // bit. On Cloudways set CAREERS_STORAGE_ROOT to …/private_html/recruitment (CLOUDWAYS-RECRUITMENT-ARCHITECTURE).
        'careers' => [
            'driver' => 'local',
            'root' => env('CAREERS_STORAGE_ROOT') ?: storage_path('app/private/careers'),
            'visibility' => 'private',
            'directory_visibility' => 'private',
            'permissions' => [
                'file' => ['public' => 0640, 'private' => 0640],
                'dir' => ['public' => 0750, 'private' => 0750],
            ],
            'serve' => false,
            'throw' => true,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => $appUrl.'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
