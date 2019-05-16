<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

/**
 * the factory for new User
 */
$factory->define(App\User::class, function (Faker $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'father_name' => $faker->firstNameMale,
        'username' => $faker->userName,
        'email' => $faker->unique()->safeEmail,
        'role_id'=>1,
        'password' => bcrypt('secret'), // secret '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm'
        'remember_token' => str_random(10),
    ];
});

/**
 *  the factory for new Role
 */
$factory->define(App\Models\Role::class, function (Faker $faker) {
    return [
        'name' => $faker->text(5),
        'description' => $faker->text(15),
    ];
});

/**
 * the factory for new Menu
 */
$factory->define(App\Models\Menu::class, function (Faker $faker) {
    return [
        "name" => $faker->text(100),
        "callback_url" => $faker->url
    ];
});

/**
 * the factory for new MenuItem
 */

$factory->define(App\Models\MenuItem::class, function (Faker $faker) {
    return [
        "menu_id"=> factory(\App\User::class),
        "name" => $faker->text(10),
        "point" => $faker->numberBetween(1,10),
        "callback_url" => $faker->url
    ];
});

/**
 * the factory for new Gateway
 */
$factory->define(App\Models\Gateway::class, function (Faker $faker) {
    return [
        "name" => $faker->name,
        "description"=>$faker->text(100),
        "link" => $faker->url,
        "default"=>$faker->boolean
    ];
});