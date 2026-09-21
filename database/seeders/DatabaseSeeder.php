<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            WeekSeeder::class,
        ]);

        Role::firstOrCreate(['name' => 'user']);
        Role::firstOrCreate(['name' => 'admin']);
        
        Permission::firstOrCreate(['name' => 'delete posts']);
        Permission::firstOrCreate(['name' => 'edit posts']);
        Permission::firstOrCreate(['name' => 'manage users']);

        //// 
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $user1 = User::find(1);
        $user2 = User::find(2);

        $user1?->assignRole('admin');
        $user2?->assignRole('user');

        $adminRole = Role::where('name', 'admin')->first();

        $adminRole?->givePermissionTo([
            'delete posts',
            'edit posts',
            'manage users'
        ]);

    }
}
