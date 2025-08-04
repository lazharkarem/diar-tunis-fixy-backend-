<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()["cache"]->forget("spatie.permission.cache");

        // Create permissions
        Permission::firstOrCreate(["name" => "manage users"]);
        Permission::firstOrCreate(["name" => "manage properties"]);
        Permission::firstOrCreate(["name" => "manage experiences"]);
        Permission::firstOrCreate(["name" => "manage service categories"]);
        Permission::firstOrCreate(["name" => "manage service providers"]);
        Permission::firstOrCreate(["name" => "manage bookings"]);
        Permission::firstOrCreate(["name" => "manage appointments"]);
        Permission::firstOrCreate(["name" => "manage payments"]);
        Permission::firstOrCreate(["name" => "manage reviews"]);
        Permission::firstOrCreate(["name" => "manage notifications"]);
        Permission::firstOrCreate(["name" => "manage messages"]);
        Permission::firstOrCreate(["name" => "view dashboard"]);

         // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(["name" => "admin"]);
        $adminRole->syncPermissions(Permission::all());

        $hostRole = Role::firstOrCreate(["name" => "host"]);
        $hostRole->syncPermissions(["manage properties", "manage experiences",
        "manage bookings", "manage reviews", "manage messages"]);

        $serviceProviderRole = Role::firstOrCreate(["name" => "service_provider"]);
        $serviceProviderRole->syncPermissions(["manage service providers",
        "manage appointments", "manage reviews", "manage messages"]);

        $guestRole = Role::firstOrCreate(["name" => "guest"]);
        // Guests typically have no special permissions initially
        $serviceCustomerRole = Role::firstOrCreate(["name" => "service_customer"]);
        // Service customers typically have no special permissions initially

        // Create a super admin user
        $adminUser = User::firstOrCreate(
            ["email" => "admin@example.com"],
                [
                "name" => "Super Admin",
                "password" => Hash::make("password"), 
                "user_type" => "admin",
                "email_verified_at" => now(),
                ]
        );
         $adminUser->assignRole("admin");

        // Create a sample host user
        $hostUser = User::firstOrCreate(
            ["email" => "host@example.com"],
            [
                "name" => "Sample Host",
                "password" => Hash::make("password"),
                "user_type" => "host",
                "email_verified_at" => now(),
            ]
        );
        $hostUser->assignRole("host");

         // Create a sample service provider user
        $serviceProviderUser = User::firstOrCreate(
             ["email" => "provider@example.com"],
                [
                    "name" => "Sample Service Provider",
                    "password" => Hash::make("password"),
                    "user_type" => "service_provider",
                    "email_verified_at" => now(),
                ]
        );
         $serviceProviderUser->assignRole("service_provider");

    }
}
