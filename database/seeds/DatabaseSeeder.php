<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(LeadsStatusTableSeed::class);
        $this->call(MessengersTableSeed::class);
        /*$this->call(RolesTableSeed::class);
        $this->call(UsersTableSeeder::class);*/

        /*
        $this->call(PermissionTableSeed::class);*/
        //$this->call(LanguagesTableSeed::class);
        //$this->call(SidebarNavTableSeed::class);
    }
}
