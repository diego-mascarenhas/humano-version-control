# Changelog

All notable changes to `humano-version-control` will be documented in this file.

## [1.3.2] - 2025-10-01

### Added
- **CRITICAL**: Added missing `spatie/laravel-permission` dependency to composer.json
- Complete translation support for comparison and restoration page headers
- Comprehensive dependency documentation in README.md

### Fixed
- Fixed missing dependency that could cause installation failures
- Centered Status column content in comparison and restoration tables
- Fixed dependency version constraints for better compatibility

### Improved
- Enhanced installation documentation with detailed requirements
- Added step-by-step dependency setup instructions
- Improved visual alignment in all table views
- Better package reliability for new installations

### Security
- Maintained mandatory permission system for data restoration security
- Role-based access control remains enforced by default

## [1.3.1] - 2025-10-01

### Added
- Restored "Restore Version A" and "Restore Version B" buttons in version comparison view
- Complete translations for restore functionality in Spanish and English

### Fixed
- Fixed `Collection::paginate()` error when viewing activities of deleted records
- Fixed null subject error in activity display with proper error handling
- Enhanced handling of deleted/missing model records in activity views

### Improved
- Better UX for deleted records with informative messages
- Manual pagination handling for single activities when subjects are deleted
- Enhanced error resilience for missing model relationships

## [1.3.0] - 2025-10-01

### Added
- Complete Spanish and English translations for all UI elements
- Proper handling of singular/plural forms in Spanish grammar
- Dynamic model resolution and improved activity logging
- Enhanced button styling and user experience improvements

### Fixed  
- Fixed htmlspecialchars() error with stdClass objects in restoration preview
- Fixed mapWithKeys() error on QueryBuilder in model statistics
- Fixed route conflicts with dynamic activity routes
- Fixed activity logging for model updates (was only showing "created" activities)
- Fixed "View Versions" link passing model display name instead of slug
- Fixed singular/plural forms: "1 Actividad" not "1 Actividades", "1 Versión" not "1 Versiones"

### Removed
- Removed non-functional "Audit & Logs" button from DataTables
- Removed "View Details" buttons from version blocks  
- Removed specialized restoration activity display (now shows as normal activities)
- Removed "Restore" buttons from version blocks and activity actions
- Removed "Show Changes" button from activity actions
- Removed button styling from activity actions (now show as simple icons)

### Improved
- Better error handling for object/array values in restoration preview
- Enhanced UI consistency across all views
- Improved translation coverage for all user-facing text
- Better handling of dynamic model classes and activity resolution

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
