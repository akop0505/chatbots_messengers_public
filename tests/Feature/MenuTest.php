<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuItem;
use App\User;
use Faker\Factory;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class MenuTest extends TestCase
{
    /**
     * A validation test
     *
     * @return void
     */
    public function testEventsDataInvalid()
    {
        $faker = Factory::create();
        $validateData = [
            [
                "name" => "",
                "callback_url" => "",
            ],
            [
                "name" => 259,
                "callback_url" => "https://test__url.com",
                "MenuItems" => [
                    [
                        "name" => "",
                        "point" => ""
                    ]
                ]
            ],
            [
                "name" => "",
                "callback_url" => "testInvalidUrl",
                "MenuItems" => [
                    [
                        "name" => 123456,
                        "callback_url" => "https://test__url.com"
                    ],
                    [
                        "name" => "No",
                        "point" => "",
                        "callback_url" => "https://test.url.com"
                    ]
                ]
            ],
        ];

        $errorKeys = [
            ['name','callback_url','MenuItems'],
            ['name','callback_url','MenuItems.0.name','MenuItems.0.point','MenuItems.0.callback_url'],
            ['name','callback_url','MenuItems.0.name','MenuItems.0.point','MenuItems.0.callback_url','MenuItems.1.name','MenuItems.1.point','MenuItems.1.callback_url']
        ];

        $user = factory(User::class)->create();

        collect($validateData)->each(function($invalidData,$key) use($user,$errorKeys,$faker){
            $response = $this
                ->actingAs($user)
                ->post(url('api/menu'),
                    $invalidData,
                    ['Authorization' => 'Bearer '.$user->createToken($faker->text(5))->accessToken, 'Accept' => 'application/json']);

            $res_object = json_decode($response->content());

            /* the assert for empty response data */
            $this->assertNotEmpty($res_object, "The response data is empty");

            $this->assertObjectHasAttribute('errorType',$res_object, "errorType: false");
            $this->assertEquals('VALIDATION_ERROR',$res_object->errorType,'errorType = VALIDATION_ERROR: false');
            $this->assertObjectHasAttribute('errors',$res_object, "errors: false");

            $responseErrorKeys = array_keys((array)$res_object->errors);

            if(count((array)$res_object->errors) <= 0) {
                $this->assertTrue(false,"The errors object is empty");
            }

            foreach ($responseErrorKeys as $attribute){
                $this->assertContains($attribute, $errorKeys[$key],"Step ".$key.". The errors object doesn't contain the attribute : ".$attribute);
            }
            $response->assertStatus(422);
        });
        $user->delete();
    }

    /**
     * The test of Create Menu
     *
     * @return void
     */
    public function testCreate()
    {
        $user = factory(User::class)->create();
        $faker = Factory::create();

        $response = $this
            ->actingAs($user)
            ->post(url('api/menu'),
                [
                    "name" => $faker->text(100),
                    "callback_url" => $faker->url,
                    "MenuItems" => [
                        [
                            "name" => $faker->text(10),
                            "point" => "2",
                            "callback_url" => $faker->url
                        ],
                        [
                            "name" => $faker->text(10),
                            "point" => "2",
                            "callback_url" => $faker->url
                        ]
                    ]
                ],
                ['Authorization' => 'Bearer '.$user->createToken($faker->text(5))->accessToken, 'Accept' => 'application/json'])
            ->assertStatus(200);

        $res_object = json_decode($response->content());
        /* the assert for empty response data */
        $this->assertNotEmpty($res_object, "The response data is empty!");

        $this->assertObjectHasAttribute("success",$res_object, "Response doesn't contain the success attribute!");
        $this->assertTrue($res_object->success, "The success must be true!");
        $this->assertObjectHasAttribute("Menu",$res_object,"Response doesn't contain the Menu Object!");

        $this
            ->actingAs($user)
            ->delete(url('api/menu/'.$res_object->Menu->id), [] ,['Authorization' => 'Bearer '.$user->createToken($faker->text(5))->accessToken, 'Accept' => 'application/json']);

        $user->delete();
    }

    /**
     * The test of Create Menu
     *
     * @return void
     */
    public function testUpdate()
    {
        $faker = Factory::create();
        $user = factory(User::class)->create();
        $menu = factory(Menu::class)->create();
        $menuItem1 = factory(MenuItem::class)->create(["menu_id"=>$menu]);
        $menuItem2 = factory(MenuItem::class)->create(["menu_id"=>$menu]);

        $dataToUpdate = [
            "name" => $faker->text(100),
            "callback_url" => $faker->url,
            "MenuItems" => [
                [
                    "id"=>$menuItem1->id,
                    "name" => $faker->text(10),
                    "point" => "1",
                    "callback_url" => $faker->url
                ],
                [
                    "name" => $faker->text(10),
                    "point" => "3",
                    "callback_url" => $faker->url
                ]
            ]
        ];
        $response = $this
            ->actingAs($user)
            ->put(url('api/menu/'.$menu->id),$dataToUpdate,
                ['Authorization' => 'Bearer '.$user->createToken($faker->text(5))->accessToken, 'Accept' => 'application/json'])
            ->assertStatus(200);

        $res_object = json_decode($response->content());
        /* the assert for empty response data */
        $this->assertNotEmpty($res_object, "The response data is empty!");

        $this->assertObjectHasAttribute("success",$res_object, "Response doesn't contain the success attribute!");
        $this->assertTrue($res_object->success, "The success must be true!");
        $this->assertObjectHasAttribute("Menu",$res_object,"Response doesn't contain the Menu Object!");
        $this->assertObjectHasAttribute("MenuItems",$res_object->Menu,"Response doesn't contain the MenuItems Object!");

        $this->assertCount(2,$res_object->Menu->MenuItems);

        $this->assertDatabaseHas('menu_items', [
            'id' => $menuItem1->id
        ]);

        $this->assertDatabaseMissing('menu_items', [
            'id' => $menuItem2->id
        ]);

        $menuItem1->delete();
        $menuItem2->delete();

        $response = $this
            ->actingAs($user)
            ->delete(url('api/menu/'.$menu->id), [] ,['Authorization' => 'Bearer '.$user->createToken($faker->text(5))->accessToken, 'Accept' => 'application/json']);

        $menu->delete();
        $user->delete();
    }



    /**
     * A test destroy Menu
     *
     * @return void
     */
    public function testDestroyEvent()
    {
        $faker = Factory::create();
        $user = factory(User::class)->create();
        $menu = factory(Menu::class)->create();
        $menuItem1 = factory(MenuItem::class)->create(["menu_id"=>$menu]);
        $menuItem2 = factory(MenuItem::class)->create(["menu_id"=>$menu]);

        $response = $this
            ->actingAs($user)
            ->delete(url('api/menu/'.$menu->id), [] ,['Authorization' => 'Bearer '.$user->createToken($faker->text(5))->accessToken, 'Accept' => 'application/json']);

        $res_object = json_decode($response->content());

        /* the assert for empty response data */
        $this->assertNotEmpty($res_object, "The response data is empty");

        $this->assertObjectHasAttribute("success",$res_object, "Response doesn't contain the success attribute!");
        $this->assertTrue($res_object->success, "The success must be true!");
        $this->assertObjectHasAttribute("id",$res_object,"Response doesn't contain the id");
        $user->delete();
    }
}
