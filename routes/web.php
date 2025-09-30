<?php

use Illuminate\Support\Facades\Route;
use Idoneo\HumanoVersionControl\Http\Controllers\VersionControlController;
use Idoneo\HumanoVersionControl\Http\Controllers\AuditTrailController;
use Idoneo\HumanoVersionControl\Http\Controllers\RestorationController;

/*
|--------------------------------------------------------------------------
| Version Control Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the version control system, audit trails,
| and restoration functionality.
|
*/

Route::middleware(['web', 'auth'])->prefix('version-control')->name('version-control.')->group(function () {
    // Main version control dashboard
    Route::get('/', [VersionControlController::class, 'index'])->name('index');

    // ✅ NUEVA RUTA PRINCIPAL - Acceso dinámico por Activity ID
    Route::get('/activity/{activityId}', [AuditTrailController::class, 'showActivity'])->name('activity.show');

    // Audit trails (mantener para compatibilidad)
    Route::get('/audit/{model?}', [AuditTrailController::class, 'index'])->name('audit.index');
    Route::get('/audit/{model}/{id}', [AuditTrailController::class, 'show'])->name('audit.show');
    Route::get('/audit/{model}/{id}/versions', [AuditTrailController::class, 'versions'])->name('audit.versions');

    // User activity
    Route::get('/users/{user}/activity', [AuditTrailController::class, 'userActivity'])->name('users.activity');
    Route::get('/activity/comparison', [AuditTrailController::class, 'compare'])->name('audit.compare');

    // Restoration
    Route::get('/restore/{model}/{id}/version/{version}', [RestorationController::class, 'preview'])->name('restore.preview');
    Route::post('/restore/{model}/{id}/version/{version}', [RestorationController::class, 'restore'])->name('restore.execute');
    Route::post('/restore/{model}/{id}/field/{field}/version/{version}', [RestorationController::class, 'restoreField'])->name('restore.field');

    // API endpoints - Dinámicos
    Route::get('/api/activities', [AuditTrailController::class, 'activities'])->name('api.activities');
    Route::get('/api/activity/{activityId}/versions', [AuditTrailController::class, 'getActivityVersions'])->name('api.activity.versions');
    Route::get('/api/{model}/{id}/versions', [VersionControlController::class, 'getVersions'])->name('api.versions');
});
