<?php

use Illuminate\Database\Seeder;

class MessengersTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //DB::table('messengers')->delete();

        $messengers = [
            [
                'alias'=>'viber',
                'name' => 'Viber',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ],
            [
                'alias'=>'whatsapp',
                'name' => 'WhatsApp',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ],
            [
                'alias'=>'telegram',
                'name' => 'Telegram',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ],
            [
                'alias'=>'vk',
                'name' => 'VK',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ],
            [
                'alias'=>'sms',
                'name' => 'SMS',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ],
            [
                'alias'=>'email',
                'name' => 'Email',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ],
            [
                'alias'=>'fb',
                'name' => 'Facebook',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]
        ];

        $aliases = DB::table('messengers')->select(['alias'])->get()->map(function($val){return $val->alias;});
        $aliasesArray = $aliases->toArray();
        foreach($messengers as $messenger){
            if(!in_array($messenger['alias'],$aliasesArray)){
                DB::table('messengers')->insert($messenger);
            }
            else{
                DB::table('messengers')->where('alias',$messenger['alias'])->update($messenger);
            }
        }
    }
}
