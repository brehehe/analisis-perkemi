<?php

use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('creates roles and permissions idempotently after a fresh migration', function () {
    $this->seed(RolePermissionSeeder::class);
    $permissionCount = Permission::query()->count();

    $this->seed(RolePermissionSeeder::class);

    $superAdmin = Role::findByName('super-admin', 'web');
    $coach = Role::findByName('coach', 'web');
    expect(Permission::query()->count())->toBe($permissionCount)
        ->and($superAdmin->hasPermissionTo('dashboard.view'))->toBeTrue()
        ->and($superAdmin->hasPermissionTo('system.settings'))->toBeTrue()
        ->and($coach->hasPermissionTo('analysis.validate'))->toBeTrue()
        ->and($coach->hasPermissionTo('system.settings'))->toBeFalse();
});
