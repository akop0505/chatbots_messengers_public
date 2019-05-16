<?php

use Illuminate\Database\Seeder;

class User_chat_info_json_oarser extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = DB::table('users_chat_info')->get()->toArray();
        foreach ($users as $user){
            $bot = json_decode($user->bot);
            $u = json_decode($user->user);
            $user->bot = json_encode($bot,JSON_UNESCAPED_UNICODE);
            $user->user = json_encode($u,JSON_UNESCAPED_UNICODE);
            DB::table('users_chat_info')->where('id',$user->id)->update((array)$user);
        }
    }
}
