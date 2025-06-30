<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            PermissionsEnum::MANAGE_ORDERS->value => [
                Role::ROLES['manager'],
                Role::ROLES['event_organizer'],
                Role::ROLES['wedding_organizer'],
            ],
            PermissionsEnum::VIEW_ANY_ORDERS->value => [
                Role::ROLES['manager'],
                Role::ROLES['event_organizer'],
                Role::ROLES['wedding_organizer'],
                Role::ROLES['client'],
            ],
            PermissionsEnum::CREATE_ORDERS->value => [
                Role::ROLES['event_organizer'],
                Role::ROLES['wedding_organizer'],
            ],
            PermissionsEnum::VIEW_ORDERS->value => [
                Role::ROLES['event_organizer'],
                Role::ROLES['wedding_organizer'],
            ],
            PermissionsEnum::EDIT_ORDERS->value => [
                Role::ROLES['event_organizer'],
                Role::ROLES['wedding_organizer'],
            ],
            PermissionsEnum::DELETE_ORDERS->value => [
                Role::ROLES['event_organizer'],
                Role::ROLES['wedding_organizer'],
            ],
            PermissionsEnum::RESTORE_ORDERS->value => [
                Role::ROLES['event_organizer'],
                Role::ROLES['wedding_organizer'],
            ],

            PermissionsEnum::MANAGE_CUSTOMERS->value => [
                Role::ROLES['manager'],
                Role::ROLES['event_organizer'],
                Role::ROLES['wedding_organizer'],
            ],
            PermissionsEnum::CREATE_CUSTOMERS->value => [
                Role::ROLES['event_organizer'],
                Role::ROLES['wedding_organizer'],
            ],
            PermissionsEnum::VIEW_CUSTOMERS->value => [
                Role::ROLES['event_organizer'],
                Role::ROLES['wedding_organizer'],
            ],
            PermissionsEnum::EDIT_CUSTOMERS->value => [
                Role::ROLES['event_organizer'],
                Role::ROLES['wedding_organizer'],
            ],
            PermissionsEnum::DELETE_CUSTOMERS->value => [
                Role::ROLES['event_organizer'],
                Role::ROLES['wedding_organizer'],
            ],
            PermissionsEnum::RESTORE_CUSTOMERS->value => [
                Role::ROLES['event_organizer'],
                Role::ROLES['wedding_organizer'],
            ],

            PermissionsEnum::MANAGE_TEMPLATES->value => [
                Role::ROLES['manager'],
                Role::ROLES['event_organizer'],
                Role::ROLES['wedding_organizer'],
            ],
            PermissionsEnum::VIEW_ANY_TEMPLATES->value => [
                Role::ROLES['manager'],
                Role::ROLES['event_organizer'],
                Role::ROLES['wedding_organizer'],
                Role::ROLES['client'],
            ],

            PermissionsEnum::MANAGE_GUESTS->value => [
                Role::ROLES['client'],
            ],
            PermissionsEnum::VIEW_ANY_GUESTS->value => [
                Role::ROLES['client'],
            ],
            PermissionsEnum::CREATE_GUESTS->value => [
                Role::ROLES['client'],
            ],
            PermissionsEnum::VIEW_GUESTS->value => [
                Role::ROLES['client'],
            ],
            PermissionsEnum::EDIT_GUESTS->value => [
                Role::ROLES['client'],
            ],
            PermissionsEnum::DELETE_ANY_GUESTS->value => [
                Role::ROLES['client'],
            ],
            PermissionsEnum::DELETE_GUESTS->value => [
                Role::ROLES['client'],
            ],
            PermissionsEnum::RESTORE_ANY_GUESTS->value => [
                Role::ROLES['client'],
            ],
            PermissionsEnum::RESTORE_GUESTS->value => [
                Role::ROLES['client'],
            ],
        ];

        foreach ($permissions as $permission => $roles) {
            $permission = Permission::firstOrCreate([
                'name' => $permission,
            ]);

            $permission->syncRoles($roles);
        }
    }
}
