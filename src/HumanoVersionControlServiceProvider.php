<?php

namespace Idoneo\HumanoVersionControl;

use Idoneo\HumanoVersionControl\Commands\HumanoVersionControlCommand;
use Idoneo\HumanoVersionControl\Models\SystemModule;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class HumanoVersionControlServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('humano-version-control')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoute('web')
            ->hasMigration('create_version_snapshots_table')
            ->hasCommand(HumanoVersionControlCommand::class);
    }

    /**
     * Ensure module registry row and permissions exist on install/boot.
     */
    public function bootingPackage()
    {
        parent::bootingPackage();

        try {
            if (Schema::hasTable('modules')) {
                // Register module if not present (works with/without host App\Models\Module)
                if (class_exists(\App\Models\Module::class)) {
                    \App\Models\Module::updateOrCreate(
                        ['key' => 'version-control'],
                        [
                            'name' => 'Version Control',
                            'icon' => 'ti ti-versions',
                            'description' => 'Advanced audit trails and data restoration system',
                            'is_core' => false,
                            'status' => 1,
                        ]
                    );
                } else {
                    SystemModule::query()->updateOrCreate(
                        ['key' => 'version-control'],
                        [
                            'name' => 'Version Control',
                            'icon' => 'ti ti-versions',
                            'description' => 'Advanced audit trails and data restoration system',
                            'is_core' => false,
                            'status' => 1,
                        ]
                    );
                }
            }

            // Create permissions if Spatie Permission is available
            if (Schema::hasTable('permissions') && class_exists(\Spatie\Permission\Models\Permission::class)) {
                $permissions = [
                    'version-control.index' => 'Access version control dashboard',
                    'version-control.audit' => 'View audit trails and activity logs',
                    'version-control.restore' => 'Access restoration functionality',
                    'restore-versions' => 'Restore data from previous versions',
                ];

                foreach ($permissions as $name => $description) {
                    \Spatie\Permission\Models\Permission::firstOrCreate(
                        ['name' => $name],
                        ['guard_name' => 'web']
                    );
                }

                // Assign permissions to admin role if it exists
                $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
                if ($adminRole) {
                    $adminRole->givePermissionTo(array_keys($permissions));
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore if host app hasn't migrated yet or if there are permission conflicts
            \Log::debug('HumanoVersionControl: Could not auto-setup module/permissions: ' . $e->getMessage());
        }
    }
}
