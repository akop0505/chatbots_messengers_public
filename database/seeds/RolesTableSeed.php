<?php

use Illuminate\Database\Seeder;

class RolesTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->delete();

        DB::table('roles')->insert(
            [
                'name'=>'admin',
                'description' => 'A Admin User',
            ]);

        DB::table('roles')->insert(
            [
                'name'=>'manager',
                'description' => 'A Manager User',
            ]);
    }
}
