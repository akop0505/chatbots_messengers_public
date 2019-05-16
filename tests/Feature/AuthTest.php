<?php

namespace Tests\Feature;

use Faker\Factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    /**
     * Validation test
     * the test data is invalid.
     */
    public function testLoginDataIsInvalid(){
        $data = [
            'username' => '',
            'password' => ''
        ];

        $response = $this->post(url('api/login'), $data);

        $res_object = json_decode($response->content());

        $this->assertObjectHasAttribute('errorType',$res_object, "errorType: false");
        $this->assertEquals('VALIDATION_ERROR',$res_object->errorType,'errorType = VALIDATION_ERROR: false');
        $this->assertObjectHasAttribute('errors',$res_object,"errors: false");
        $this->assertObjectHasAttribute('username',$res_object->errors,"errors[username]: false");
        $this->assertObjectHasAttribute('password',$res_object->errors,"errors[password]: false");

        $response->assertStatus(422);
    }

    /**
     * the test is login failed.
     *
     * @return void
     */
    public function testLoginFailed()
    {
        $faker = Factory::create();

        $data = [
            'username' => $faker->text(8),
            'password' => $faker->text(8)
        ];

        $response = $this->post(url('api/login'), $data);
        $res_object = json_decode($response->content());

        $this->assertObjectHasAttribute('errorType',$res_object, "errorType: false");
        $this->assertEquals('INCORRECT_DATA_ERROR',$res_object->errorType,'errorType = LOGIN_ERROR: false');

        $this->assertObjectHasAttribute('errors',$res_object, "errors: false");

        $response->assertStatus(401);

    }

    /**
     * A login api test.
     *
     * @return void
     */
    public function testLogin()
    {
        $data = [
            'username' => 'superadmin',
            'password' => 'secret'
        ];

        $response = $this->post(url('api/login'), $data)
            ->assertStatus(200);
    }

    /**
     * A logout api test.
     *
     * @return void
     */
    public function testLogout()
    {
        $data = [
            'username' => 'superadmin',
            'password' => 'secret'
        ];

        $loginResponse = $this->post(url('api/login'), $data)
            ->assertStatus(200);
        $login_response_data= json_decode($loginResponse->content());

        $response = $this->get(url('api/logout'),[
            "Content-Type"=>"application/json",
            "Authorization"=>"Bearer ".$login_response_data->token
        ]);

        $response->assertStatus(200);

        $res_object = json_decode($response->content());

        $this->assertObjectHasAttribute('success',$res_object, "success: false");
        $this->assertObjectHasAttribute('messages',$res_object, "messages: false");

    }
}
