<?php

use Illuminate\Database\Seeder;

class PermissionTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permissions')->delete();

        DB::table('permissions')->insert(
            [
                'alias'=>'send',
                'name' => 'Отправка',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);

        DB::table('permissions')->insert(
            [
                'alias'=>'get',
                'name' => 'Получать',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);

        DB::table('permissions')->insert(
            [
                'alias'=>'api_access',
                'name' => 'Доступ через апи.',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);

    }
}
