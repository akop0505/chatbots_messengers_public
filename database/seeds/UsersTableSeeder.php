<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_employee = \App\Models\Role::where('name', 'admin')->first();

        $admin = new \App\User();
        $admin->username = 'superadmin';
        $admin->first_name = 'super admin';
        $admin->email = 'admin@test.com';
        $admin->password = bcrypt('secret');
        $admin->role_id = $role_employee->id;
        $admin->save();
    }
}
