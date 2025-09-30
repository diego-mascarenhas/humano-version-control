<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Version Control Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the Humano Version Control package.
    |
    */

    'name' => 'Humano Version Control',
    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Activity Log Integration
    |--------------------------------------------------------------------------
    |
    | Settings for integrating with spatie/laravel-activitylog
    |
    */
    'activity_log' => [
        // Models that should have enhanced version control
        'trackable_models' => [
            // 'App\Models\Contact',
            // 'App\Models\Project',
            // 'App\Models\Message',
        ],

        // Number of versions to keep per model instance
        'versions_to_keep' => 50,

        // Automatically clean up old versions
        'auto_cleanup' => true,

        // Days to keep old versions before cleanup
        'cleanup_after_days' => 90,
    ],

    /*
    |--------------------------------------------------------------------------
    | Snapshot Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for creating snapshots of model states
    |
    */
    'snapshots' => [
        // Create full snapshots instead of just diffs
        'create_full_snapshots' => true,

        // Compress snapshot data
        'compress_snapshots' => true,

        // Include related models in snapshots
        'include_relations' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Restoration Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the restoration functionality
    |
    */
    'restoration' => [
        // Require confirmation for restorations
        'require_confirmation' => true,

        // Log all restoration activities
        'log_restorations' => true,

        // Allow partial field restoration
        'allow_partial_restore' => true,

        // Roles that can perform restorations
        'authorized_roles' => [
            'admin',
            'super-admin',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the version control interface
    |
    */
    'ui' => [
        // Show version control in DataTables by default
        'show_in_datatables' => true,

        // Maximum versions to show in dropdowns
        'max_versions_in_dropdown' => 20,

        // Enable real-time version updates
        'real_time_updates' => false,
    ],
];
