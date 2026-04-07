<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class SsoPermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = config('acl.permissions', []);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        $this->command->info('  SSO permissions seeded: ' . count($permissions) . ' permissions.');
    }
}
