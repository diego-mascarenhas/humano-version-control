<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Version Control Configuration
    |--------------------------------------------------------------------------
    |
    | ✅ Configuration options for the Humano Version Control package.
    | Completely dynamic and portable across different Laravel applications.
    |
    */

    'name' => 'Humano Version Control',
    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | ✅ Model class for users in your application - Dynamic detection
    */
    'user_model' => '\\App\\Models\\User',

    /*
    |--------------------------------------------------------------------------
    | Activity Model
    |--------------------------------------------------------------------------
    |
    | ✅ Model class for activities (Spatie ActivityLog) - Configurable
    */
    'activity_model' => '\\Spatie\\Activitylog\\Models\\Activity',

    /*
    |--------------------------------------------------------------------------
    | Date & Time Configuration
    |--------------------------------------------------------------------------
    |
    | ✅ Formats and settings for displaying activities
    */
    'date_format' => 'Y-m-d H:i:s',
    'timezone' => null, // null = app default

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | ✅ Items per page for activity lists
    */
    'per_page' => 20,
    'max_versions_per_page' => 50,

    /*
    |--------------------------------------------------------------------------
    | Dynamic Model Detection
    |--------------------------------------------------------------------------
    |
    | ✅ AUTO-DISCOVERY: No need to manually map models!
    | The system automatically detects all models with activity logs
    |
    */
    'auto_discover_models' => true,

    /*
    |--------------------------------------------------------------------------
    | Activity Log Integration
    |--------------------------------------------------------------------------
    |
    | ✅ Settings for integrating with spatie/laravel-activitylog
    | DYNAMIC: Works with any models that use ActivityLog trait
    |
    */
    'activity_log' => [
        // Number of versions to keep per model instance
        'versions_to_keep' => 100,

        // Automatically clean up old versions
        'auto_cleanup' => true,

        // Days to keep old versions before cleanup
        'cleanup_after_days' => 365,

        // Maximum activities to load in DataTables (performance)
        'max_activities_load' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Snapshot Configuration
    |--------------------------------------------------------------------------
    |
    | ✅ Settings for creating snapshots of model states
    |
    */
    'snapshots' => [
        // Create full snapshots instead of just diffs
        'create_full_snapshots' => true,

        // Compress snapshot data
        'compress_snapshots' => false,

        // Include related models in snapshots
        'include_relations' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Restoration Settings
    |--------------------------------------------------------------------------
    |
    | ✅ Configuration for the restoration functionality
    |
    */
    'restoration' => [
        // Require confirmation for restorations
        'require_confirmation' => true,

        // Log all restoration activities
        'log_restorations' => true,

        // Allow partial field restoration
        'allow_partial_restore' => true,

        // Roles that can perform restorations (dynamic - checks permissions)
        'authorized_roles' => [
            'admin',
            'super-admin',
        ],

        // Permissions that allow restoration
        'restoration_permissions' => [
            'restore-versions',
            'version-control.restore',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    |
    | ✅ Settings for the version control interface
    |
    */
    'ui' => [
        // Show version control in DataTables by default
        'show_in_datatables' => true,

        // Maximum versions to show in dropdowns
        'max_versions_in_dropdown' => 25,

        // Enable real-time version updates
        'real_time_updates' => false,

        // Default table length for DataTables
        'datatable_length' => 10,

        // Enable advanced search filters
        'enable_advanced_filters' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | ✅ NUEVA CONFIGURACIÓN DINÁMICA
    |--------------------------------------------------------------------------
    |
    | Configuraciones específicas para el acceso dinámico por Activity ID
    |
    */
    'dynamic_access' => [
        // Enable direct access by Activity ID (recommended)
        'enabled' => true,

        // URL structure: /version-control/activity/{id}
        'url_pattern' => 'activity/{activityId}',

        // Auto-detect model display names
        'auto_model_names' => true,

        // Cache model type mappings for performance
        'cache_model_types' => true,
        'cache_duration' => 3600, // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | ✅ Settings to optimize performance with large datasets
    |
    */
    'performance' => [
        // Eager load relationships in queries
        'eager_load_relations' => ['causer', 'subject'],

        // Use pagination for large result sets
        'force_pagination' => true,

        // Cache frequently accessed data
        'enable_caching' => false,

        // Database query optimization
        'optimize_queries' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | ✅ Security configurations for audit access
    |
    */
    'security' => [
        // Restrict access to authenticated users only
        'require_authentication' => true,

        // Check permissions for audit access
        'check_permissions' => true,

        // Log audit trail access
        'log_audit_access' => false,

        // Rate limiting for API endpoints
        'rate_limit' => [
            'enabled' => false,
            'max_requests' => 100,
            'per_minutes' => 60,
        ],
    ],
];