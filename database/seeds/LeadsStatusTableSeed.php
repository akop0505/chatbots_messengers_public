<?php

use Illuminate\Database\Seeder;

class LeadsStatusTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(DB::table('leads_status_dict')->get()->count() == 0) {

            DB::table('leads_status_dict')->insert([
                ['name' => 'Создан'],
                ['name' => 'Удален'],
                ['name' => 'Неуспешный'],
                ['name' => 'Успешный'],
            ]);

        }
    }
}
