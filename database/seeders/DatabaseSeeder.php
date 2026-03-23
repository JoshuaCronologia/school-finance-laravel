<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Order matters: master data first, then roles/permissions (creates admin user),
     * then transactions (references master data and user).
     */
    public function run(): void
    {
        $this->call([
            MasterDataSeeder::class,
            RolePermissionSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
