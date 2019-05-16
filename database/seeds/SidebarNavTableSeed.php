<?php

use Illuminate\Database\Seeder;


class SidebarNavTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sidebar_navs')->delete();

        $routes = [
            [
                'label'=>'Главная',
                'path'=>'/dashboard',
                'name' => 'dashboard',
            ],
            //Projects
            [
                'label'=>'Проекты',
                'path'=>'/projects',
                'name' => 'project',
            ],
            /*[
                'path'=>'/projects/form/:id?',
                'name' => 'project.form',
            ],*/
            // Templates
            [
                'label'=>'Шаблоны',
                'path'=>'/control/templates',
                'name' => 'template',
            ],
            /*[
                'path'=>'/control/templates/form/:id?',
                'name' => 'template.form',
            ],*/
            // self Manager Menu
            [
                'label'=>'Меню',
                'path'=>'/control/self-management-menu',
                'name' => 'self-management-menu',
            ],
            /*[
                'path'=>'/control/self-management-menu/form?id',
                'name' => 'self-management-menu.form',
            ],*/

            //settings
            [
                'label'=>'Пользователи',
                'path'=>'/project-settings/users',
                'name' => 'user',
            ],
            /*[
                'path'=>'/project-settings/users/form?id',
                'name' => 'user.form',
            ],*/

            [
                'label'=>'Роли',
                'path'=>'/project-settings/roles',
                'name' => 'role',
            ],
            /*[
                'path'=>'/project-settings/roles/form?id',
                'name' => 'role.form',
            ],*/

            [
                'label'=>'События',
                'path'=>'/project-settings/events',
                'name' => 'event',
            ],
            /*[
                'path'=>'/project-settings/events/form?id',
                'name' => 'event.form',
            ],*/

            [
                'label'=>'Получатели',
                'path'=>'/project-settings/receivers',
                'name' => 'receiver',
            ],
            /*[
                'path'=>'/project-settings/receivers/form?id',
                'name' => 'receiver.form',
            ],*/

            [
                'label'=>'Справочник допустимых шлюзов',
                'path'=>'/project-settings/gateways',
                'name' => 'gateway',
            ],
            /*[
                'path'=>'/project-settings/gateways/form?id',
                'name' => 'gateway.form',
            ],*/
        ];
        try{
            DB::beginTransaction();
            foreach($routes as $route){
                DB::table('sidebar_navs')->insert($route);
            }
            DB::commit();
        }
        catch (Exception $exception){
            DB::rollback();

        }
    }
}
