# Changelog

All notable changes to `humano-version-control` will be documented in this file.

## [1.0.0] - 2025-09-30

### Added
- Initial release
- Advanced audit trails using spatie/laravel-activitylog
- Version history tracking for all models
- Data restoration capabilities with field-level granularity
- User activity monitoring and filtering
- Version comparison functionality
- Automatic module and permission registration
- Complete UI with DataTables integration
- Comprehensive permission system (index, audit, restore)

### Features
- **Dashboard**: Statistics overview and recent activity
- **Audit Trail**: Complete activity logs with filtering
- **Restoration**: Preview and restore data from any version
- **User Activity**: Track changes by specific users
- **Version Comparison**: Compare different versions side by side
- **API Endpoints**: RESTful API for activities and versions

### Dependencies
- Laravel 10+ support
- spatie/laravel-activitylog for audit trails
- yajra/laravel-datatables-oracle for data tables
- Compatible with Vuexy template UI
