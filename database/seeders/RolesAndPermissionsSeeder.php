<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()["cache"]->forget("spatie.permission.cache");

        // Create permissions with sanctum guard
        $permissions = [
            "manage users",
            "manage properties",
            "manage experiences",
            "manage service categories",
            "manage service providers",
            "manage bookings",
            "manage appointments",
            "manage payments",
            "manage reviews",
            "manage notifications",
            "manage messages",
            "view dashboard"
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'sanctum'
            ]);
        }

        // Create roles with sanctum guard and assign permissions
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'sanctum'
        ]);
        $adminRole->syncPermissions(Permission::all());

        $hostRole = Role::firstOrCreate([
            'name' => 'host',
            'guard_name' => 'sanctum'
        ]);
        $hostRole->syncPermissions([
            "manage properties", "manage experiences",
            "manage bookings", "manage reviews", "manage messages"
        ]);

        $serviceProviderRole = Role::firstOrCreate([
            'name' => 'service_provider',
            'guard_name' => 'sanctum'
        ]);
        $serviceProviderRole->syncPermissions([
            "manage service providers", "manage appointments",
            "manage reviews", "manage messages"
        ]);

        // Create guest and service customer roles
        $guestRole = Role::firstOrCreate([
            'name' => 'guest',
            'guard_name' => 'sanctum'
        ]);

        $serviceCustomerRole = Role::firstOrCreate([
            'name' => 'service_customer',
            'guard_name' => 'sanctum'
        ]);
        // Service customers typically have no special permissions initially

        // Create users and assign roles using direct database operations
        $users = [
            [
                'email' => 'admin@example.com',
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'user_type' => 'admin',
                'is_active' => true,
                'role_id' => $adminRole->id
            ],
            [
                'email' => 'host@example.com',
                'name' => 'Host User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'user_type' => 'host',
                'is_active' => true,
                'role_id' => $hostRole->id
            ],
            [
                'email' => 'provider@example.com',
                'name' => 'Service Provider',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'user_type' => 'service_provider',
                'is_active' => true,
                'role_id' => $serviceProviderRole->id
            ],
            [
                'email' => 'guest@example.com',
                'name' => 'Guest User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'user_type' => 'guest',
                'is_active' => true,
                'role_id' => $guestRole->id
            ],
            [
                'email' => 'customer@example.com',
                'name' => 'Service Customer',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'user_type' => 'service_customer',
                'is_active' => true,
                'role_id' => $serviceCustomerRole->id
            ]
        ];

        foreach ($users as $userData) {
            $roleId = $userData['role_id'];
            unset($userData['role_id']);
            
            // Create or update user
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            
            // Assign role using direct DB insert
            if (!DB::table('model_has_roles')
                ->where('role_id', $roleId)
                ->where('model_type', 'App\\Models\\User')
                ->where('model_id', $user->id)
                ->exists()) {
                
                DB::table('model_has_roles')->insert([
                    'role_id' => $roleId,
                    'model_type' => 'App\\Models\\User',
                    'model_id' => $user->id
                ]);
            }
        }
    }
}
