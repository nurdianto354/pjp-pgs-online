<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'email'     => 'superadmin@gmail.com',
                'password'  => bcrypt('qwer!234'),
                'nama'      => 'Superadmin',
                'status'    => true,
            ]
        );

        //assign permission to role
        $role = Role::where('name', 'superadmin')->first() ?: Role::first();
        if ($role) {
            $permissions = Permission::all();
            $role->syncPermissions($permissions);
            $user->assignRole($role->name);
        }
    }
}
