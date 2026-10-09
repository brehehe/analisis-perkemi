<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'athletes.view',
            'athletes.create',
            'athletes.update',
            'athletes.delete',
            'coaches.view',
            'coaches.create',
            'coaches.update',
            'coaches.delete',
            'clubs.view',
            'clubs.create',
            'clubs.update',
            'clubs.delete',
            'events.view',
            'events.create',
            'events.update',
            'events.delete',
            'matches.view',
            'matches.create',
            'matches.update',
            'matches.delete',
            'videos.view',
            'videos.upload',
            'videos.delete',
            'videos.analyze',
            'analysis.view',
            'analysis.validate',
            'opportunities.view',
            'opportunities.validate',
            'strategies.view',
            'strategies.generate',
            'reports.view',
            'reports.create',
            'reports.update',
            'reports.export',
            'history.view',
            'system.settings',
            'system.backup',
            'system.audit',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $rolePermissions = [
            'super-admin' => $permissions,
            'admin' => [
                'dashboard.view', 'athletes.view', 'athletes.create', 'athletes.update', 'athletes.delete',
                'coaches.view', 'coaches.create', 'coaches.update', 'coaches.delete',
                'clubs.view', 'clubs.create', 'clubs.update', 'clubs.delete',
                'events.view', 'events.create', 'events.update', 'events.delete',
                'matches.view', 'matches.create', 'matches.update', 'matches.delete',
                'videos.view', 'videos.upload', 'videos.delete', 'videos.analyze',
                'analysis.view', 'opportunities.view', 'strategies.view', 'reports.view', 'history.view',
            ],
            'coach' => [
                'dashboard.view', 'athletes.view', 'athletes.create', 'athletes.update',
                'matches.view', 'matches.create', 'matches.update',
                'videos.view', 'videos.upload', 'videos.analyze',
                'analysis.view', 'analysis.validate',
                'opportunities.view', 'opportunities.validate',
                'strategies.view', 'strategies.generate',
                'reports.view', 'reports.create', 'reports.update', 'reports.export', 'history.view',
            ],
            'performance-analyst' => [
                'dashboard.view', 'athletes.view', 'matches.view', 'videos.view',
                'analysis.view', 'analysis.validate',
                'opportunities.view', 'opportunities.validate',
                'strategies.view', 'reports.view', 'history.view',
            ],
            'athlete' => ['dashboard.view', 'history.view'],
        ];

        foreach ($rolePermissions as $roleName => $assignedPermissions) {
            Role::findOrCreate($roleName, 'web')->syncPermissions($assignedPermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
