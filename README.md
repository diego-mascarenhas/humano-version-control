# Humano Version Control

[![Latest Version on Packagist](https://img.shields.io/packagist/v/idoneo/humano-version-control.svg?style=flat-square)](https://packagist.org/packages/idoneo/humano-version-control)
[![Total Downloads](https://img.shields.io/packagist/dt/idoneo/humano-version-control.svg?style=flat-square)](https://packagist.org/packages/idoneo/humano-version-control)

Advanced version control and audit system with restoration capabilities for Laravel applications. Built on top of `spatie/laravel-activitylog`.

## Features

- 🔍 **Comprehensive Audit Trails**: Track all changes across your models
- 🔄 **Data Restoration**: Restore complete records or individual fields from any version
- 👤 **User Activity Tracking**: View all activities by specific users
- 📊 **Visual Comparisons**: Side-by-side comparison of different versions
- 🛡️ **Permission Control**: Role-based access to restoration features
- 📈 **Dashboard Analytics**: Overview of system activity and statistics
- 🗂️ **Model Filtering**: Filter activities by model type, user, date range
- 💾 **Snapshot System**: Optional full snapshots for complex restoration scenarios

## Installation

You can install the package via composer:

```bash
composer require idoneo/humano-version-control
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="humano-version-control-migrations"
php artisan migrate
```

Optionally, publish the config file:

```bash
php artisan vendor:publish --tag="humano-version-control-config"
```

## Configuration

The package automatically integrates with `spatie/laravel-activitylog`. Configure which models should have enhanced version control in the config file:

```php
'activity_log' => [
    'trackable_models' => [
        'App\Models\Contact',
        'App\Models\Project',
        'App\Models\Message',
    ],
    'versions_to_keep' => 50,
    'auto_cleanup' => true,
    'cleanup_after_days' => 90,
],
```

## Usage

### Basic Setup

Make sure your models use the `LogsActivity` trait from spatie/laravel-activitylog:

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Contact extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
```

### Accessing the Interface

- **Dashboard**: `/version-control` - Overview and statistics
- **Audit Trail**: `/version-control/audit` - Complete activity log
- **User Activity**: `/version-control/users/{id}/activity` - Activities by specific user
- **Model History**: `/version-control/audit/{model}/{id}` - History for specific record

### Restoration

The package provides several restoration options:

1. **Full Record Restoration**: Restore all fields from a specific version
2. **Selective Field Restoration**: Choose which fields to restore
3. **Preview Mode**: See exactly what will change before restoring

### API Endpoints

```php
// Get versions for a specific model instance
GET /version-control/api/{model}/{id}/versions

// Get activities with DataTables support
GET /version-control/api/activities
```

### Commands

```bash
# Show module status
php artisan humano-version-control status

# Clean up old versions
php artisan humano-version-control cleanup

# Show restore help
php artisan humano-version-control restore
```

## Permissions

Configure which roles can perform restorations:

```php
'restoration' => [
    'authorized_roles' => [
        'admin',
        'super-admin',
    ],
],
```

## UI Features

- **Responsive DataTables**: Sortable, filterable activity tables
- **Advanced Filtering**: Filter by model, user, date range
- **Real-time Updates**: Optional real-time activity updates
- **Visual Indicators**: Clear status indicators for changes
- **Bulk Operations**: Select multiple fields for restoration

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Diego Adrián Mascarenhas Goytía](https://github.com/diego-mascarenhas)
- [All Contributors](../../contributors)

## License

The GNU Affero General Public License (AGPL). Please see [License File](LICENSE.md) for more information.
