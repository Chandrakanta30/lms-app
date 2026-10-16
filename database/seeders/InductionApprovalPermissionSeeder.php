<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class InductionApprovalPermissionSeeder extends Seeder
{
    /** Department codes map to the existing roles allowed to sign induction records. */
    private const APPROVERS = [
        'AMD' => ['Executive'],
        'DQA' => ['Executive', 'Manager', 'Executive-E3'],
        'ASD' => ['Manager 1', 'Manager 2'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Ensure the approver roles exist before attaching their permissions.
        foreach (array_unique(array_merge(...array_values(self::APPROVERS))) as $roleName) {
            $roleExists = Role::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($roleName)])
                ->where('guard_name', 'web')
                ->exists();

            if (! $roleExists) {
                Role::findOrCreate($roleName, 'web');
            }
        }

        foreach (self::APPROVERS as $departmentCode => $roleNames) {
            foreach ($roleNames as $roleName) {
                $role = Role::query()
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($roleName)])
                    ->where('guard_name', 'web')
                    ->first();

                if (! $role) {
                    $this->command?->warn("Skipping {$departmentCode}/{$roleName}: role does not exist.");
                    continue;
                }

                $permission = Permission::findOrCreate(
                    "training.induction.approve.{$departmentCode}." . str_replace(' ', '-', mb_strtolower($roleName)),
                    'web'
                );
                $role->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
