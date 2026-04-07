<?php

namespace Database\Seeders;

use App\Services\Users\BranchUser;
use App\Models\Employee;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class SsoBranchUserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure SSO permissions exist first
        $this->call(SsoPermissionSeeder::class);

        // ─── EMPLOYEES ─────────────────────────────────────────────
        $employees = [
            [
                'parent_id'   => 'EMP-001',
                'name'        => 'Maria Santos',
                'email'       => 'maria.santos@school.edu.ph',
                'branch_code' => 'main',
                'permissions'  => ['accounting', 'request', 'announcement', 'announcement history', 'contacts'],
            ],
            [
                'parent_id'   => 'EMP-002',
                'name'        => 'Carlos Reyes',
                'email'       => 'carlos.reyes@school.edu.ph',
                'branch_code' => 'main',
                'permissions'  => ['accounting', 'contacts'],
            ],
            [
                'parent_id'   => 'EMP-003',
                'name'        => 'Ana Mendoza',
                'email'       => 'ana.mendoza@school.edu.ph',
                'branch_code' => 'main',
                'permissions'  => ['accounting', 'request', 'announcement', 'announcement history', 'contacts', 'access rights', 'setup'],
            ],
        ];

        foreach ($employees as $emp) {
            $perms = $emp['permissions'];
            unset($emp['permissions']);

            $bu = BranchUser::updateOrCreate(
                ['parent_id' => $emp['parent_id'], 'parent_type' => Employee::class, 'branch_code' => $emp['branch_code']],
                ['name' => $emp['name'], 'email' => $emp['email'], 'is_active' => true]
            );
            $bu->syncPermissions($perms);

            $this->command->info("  Employee: {$bu->name} ({$bu->parent_id}) — " . implode(', ', $perms));
        }

        // ─── STUDENTS ──────────────────────────────────────────────
        $students = [
            [
                'parent_id'   => 'STU-2026-0001',
                'name'        => 'Juan Dela Cruz',
                'email'       => 'juan.delacruz@student.school.edu.ph',
                'branch_code' => 'main',
                'permissions'  => ['test'],
            ],
            [
                'parent_id'   => 'STU-2026-0002',
                'name'        => 'Rizalyn Garcia',
                'email'       => 'rizalyn.garcia@student.school.edu.ph',
                'branch_code' => 'main',
                'permissions'  => ['test'],
            ],
        ];

        foreach ($students as $stu) {
            $perms = $stu['permissions'];
            unset($stu['permissions']);

            $bu = BranchUser::updateOrCreate(
                ['parent_id' => $stu['parent_id'], 'parent_type' => Student::class, 'branch_code' => $stu['branch_code']],
                ['name' => $stu['name'], 'email' => $stu['email'], 'is_active' => true]
            );
            $bu->syncPermissions($perms);

            $this->command->info("  Student: {$bu->name} ({$bu->parent_id}) — " . implode(', ', $perms));
        }

        $this->command->info('');
        $this->command->info('  ✓ ' . count($employees) . ' employees + ' . count($students) . ' students created.');
        $this->command->info('');
        $this->command->info('  Test URLs (local only):');
        $this->command->info('    Employee: /dev/sso-test/employee/main');
        $this->command->info('    Student:  /dev/sso-test/student/main');
        $this->command->info('');
        $this->command->info('  Direct login URLs:');
        foreach ($employees as $emp) {
            $hash = md5($emp['parent_id']);
            $this->command->info("    {$emp['name']}: /branch-login/employee/main/{$hash}");
        }
        foreach ($students as $stu) {
            $hash = md5($stu['parent_id']);
            $this->command->info("    {$stu['name']}: /branch-login/student/main/{$hash}");
        }
    }
}
