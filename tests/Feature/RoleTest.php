<?php

namespace Tests\Feature;

use App\Models\Role;
use App\User;
use Faker\Factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleTest extends TestCase
{

    /**
     * The test of for invalid data
     *
     * @return void
     *
     */
    public function testRolesDataInvalid()
    {
        $faker = Factory::create();
        $validateData = [
            [
                'name'=>'',
                'description'=>'',
            ],
            [
                'name'=>'',
                'description'=>$faker->text(5)
            ],
            [
                'name'=>$faker->text(5),
                'description'=>"",
            ],
            []

        ];
        $required = ['name','description'];

        $user = factory(User::class)->create();

        collect($validateData)->each(function($invalidData) use($user,$required,$faker){
            $response = $this->actingAs($user)
                ->post(url('api/roles'),
                    $invalidData,
                    ['Authorization' => 'Bearer '.$user->createToken($faker->text(5))->accessToken, 'Accept' => 'application/json']);

            $res_object = json_decode($response->content());

            /* the assert for empty response data */
            $this->assertNotEmpty($res_object, "The response data is empty");

            $this->assertObjectHasAttribute('errorType',$res_object, "errorType: false");
            $this->assertEquals('VALIDATION_ERROR',$res_object->errorType,'errorType = VALIDATION_ERROR: false');
            $this->assertObjectHasAttribute('errors',$res_object, "errors: false");

            $attributes = array_keys((array)$res_object->errors);

            if(count((array)$res_object->errors) <= 0){
                $this->assertTrue(false,"The errors object is empty");
            }
            foreach ($attributes as $attribute){
                $this->assertArrayNotHasKey($attribute, $required,"The errors object doesn't contain the attribute : ".$attribute);
            }
            $response->assertStatus(422);
        });
        $user->delete();
    }


    /**
     * The test of Create Role
     *
     * @return void
     */
    public function testCreate()
    {
        $user = factory(User::class)->create();
        $faker = Factory::create();

        $response = $this->actingAs($user)
            ->post(url('api/roles'),
                [
                    'name'=>$faker->title,
                    'description'=>$faker->text(25),
                ],
                ['Authorization' => 'Bearer '.$user->createToken($faker->text(5))->accessToken, 'Accept' => 'application/json'])
            ->assertStatus(200);

        $res_object = json_decode($response->content());

        /* the assert for empty response data */
        $this->assertNotEmpty($res_object, "The response data is empty!");

        $this->assertObjectHasAttribute("success",$res_object, "Response doesn't contain the success attribute!");
        $this->assertTrue($res_object->success, "The success must be true!");
        $this->assertObjectHasAttribute("Role",$res_object,"Response doesn't contain the Role Object!");

        $user->delete();
    }

    /**
     * The test of Update Role
     *
     * @return void
     */
    public function testUpdate()
    {
        $user = factory(User::class)->create();
        $role = factory(Role::class)->create();
        $faker = Factory::create();

        $response = $this->actingAs($user)
            ->put(url('api/roles/'.$role),
                [
                    'name'=>$faker->title,
                    'description'=>$faker->text(25),
                ],
                ['Authorization' => 'Bearer '.$user->createToken($faker->text(5))->accessToken, 'Accept' => 'application/json'])
            ->assertStatus(200);

        $res_object = json_decode($response->content());

        /* the assert for empty response data */
        $this->assertNotEmpty($res_object, "The response data is empty!");

        $this->assertObjectHasAttribute("success",$res_object, "Response doesn't contain the success attribute!");
        $this->assertTrue($res_object->success, "The success must be true!");
        $this->assertObjectHasAttribute("Role",$res_object,"Response doesn't contain the Role Object!");

        $user->delete();
    }

    /**
     * The test of Destroy Role
     *
     * @return void
     */
    public function testDestroy() {
        $user = factory(User::class)->create();
        $role = factory(Role::class)->create();
        $faker = Factory::create();

        $response = $this->actingAs($user)
            ->delete(url('api/roles/'.$role->id),
                [
                    'name'=>$faker->title,
                    'description'=>$faker->text(25),
                ],
                ['Authorization' => 'Bearer '.$user->createToken($faker->text(5))->accessToken, 'Accept' => 'application/json'])
            ->assertStatus(200);

        $res_object = json_decode($response->content());

        $this->assertNotEmpty($res_object, "The response data is empty");

        $this->assertObjectHasAttribute("success",$res_object, "Response doesn't contain the attribute 'success'!");
        $this->assertTrue($res_object->success, "The success must be true!");

        $this->assertObjectHasAttribute("id",$res_object,"Response doesn't contain the attribute 'id'!");

        $user->delete();
    }
}
