<?php

namespace Tests\Feature;

use App\User;
use Faker\Factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{

    /**
     * A Validation Failed test
     * @return void
     */
    public function testCreateUserValidationFailed(){
        $data = [
            'first_name' => '',
            'last_name' => '',
            'father_name' => '',
            'phone' => '',
            'email' => '',
            'username' => '',
            'password' => '',
            'role'=>''
        ];

        $required = [
            'first_name',
            'last_name',
            'father_name',
            'email',
            'username',
            'password',
            'role'
        ];

        $response = $this->post(url('api/users'), $data);

        $res_object = json_decode($response->content());

        /* the assert for empty response data */
        $this->assertNotEmpty($res_object, "The response data is empty");

        $this->assertObjectHasAttribute('errorType',$res_object, "errorType: false");
        $this->assertEquals('VALIDATION_ERROR',$res_object->errorType,'errorType = VALIDATION_ERROR: false');
        $this->assertObjectHasAttribute('errors',$res_object,"errors: false");

        $errorsAttributes = array_keys((array)$res_object->errors);
        foreach ($required as $field){
                $this->assertArrayNotHasKey($field, $errorsAttributes,"The errors object doesn't contain the attribute : ".$field);

        }
        $response->assertStatus(422);
    }

    /**
     * a test create new User
     *
     * @return void
     */
    public function testCreteUser()
    {
        $faker = Factory::create();
        $data = [
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'father_name' => $faker->firstNameMale,
            'phone' => $faker->phoneNumber,
            'email' => $faker->unique()->safeEmail,
            'username' => $faker->userName,
            'password' => $faker->password,
            'role_id'=>$faker->randomElement([1, 2])
        ];
        $user = factory(User::class)->create();
        $response = $this->post(url('api/users'),$data);

        $res_object = json_decode($response->content());

        /* the assert for empty response data */
        $this->assertNotEmpty($res_object, "The response data is empty");

        $this->assertObjectHasAttribute("success",$res_object, "Response doesn't contain the success attribute!");
        $this->assertTrue($res_object->success, "The success must be true!");
        $this->assertObjectHasAttribute("User",$res_object,"Response doesn't contain the User Object!");

        $dataKey = array_keys((array)$data);
        $responseUserDataKey = array_keys((array)$data);
        foreach ($dataKey as $dk){
            $this->assertArrayNotHasKey($dk, $responseUserDataKey ,"The User object doesn't contain the attribute : ".$dk);
        }
        $user->delete();
    }

    /**
     * A test update User
     *
     * @return void
     */
    public function testUpdateUser()
    {
        $faker = Factory::create();
        $data = [
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'father_name' => $faker->firstNameMale,
            'phone' => $faker->phoneNumber,
            'email' => $faker->unique()->safeEmail,
            'username' => $faker->userName,
            'password' => $faker->password,
            'role_id'=>$faker->randomElement([1, 2]),
        ];
        $user = factory(User::class)->create();
        $response = $this->put(url('api/users/'.$user->id),$data);

        $res_object = json_decode($response->content());

        /* the assert for empty response data */
        $this->assertNotEmpty($res_object, "The response data is empty");

        $this->assertObjectHasAttribute("success",$res_object, "Response doesn't contain the success attribute!");
        $this->assertTrue($res_object->success, "The success must be true!");
        $this->assertObjectHasAttribute("User",$res_object,"Response doesn't contain the User Object!");
        $user->delete();
    }

    /**
     * A test destroy User
     *
     * @return void
     */
    public function testDestroyUser()
    {
        $user = factory(User::class)->create();
        $response = $this->delete(url('api/users/'.$user->id));

        $res_object = json_decode($response->content());

        /* the assert for empty response data */
        $this->assertNotEmpty($res_object, "The response data is empty");

        $this->assertObjectHasAttribute("success",$res_object, "Response doesn't contain the success attribute!");
        $this->assertTrue($res_object->success, "The success must be true!");
        $this->assertObjectHasAttribute("User",$res_object,"Response doesn't contain the User Object!");
        $user->delete();
    }

}
