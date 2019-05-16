<?php

namespace Tests\Feature;

use App\Models\Gateway;
use App\User;
use Faker\Factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GatewayTest extends TestCase
{
    /**
     * A validation test
     *
     * @return void
     */
    public function testGatewayDataInvalid()
    {
        $faker = Factory::create();
        $validateData = [
            [],
            [
                "name" => "",
                "description" => "",
                "link" => "",
                "default"=>""
            ],
            [
                "name" => 259,
                "description" => 112,
                "link" => "https://test__url.com",
                "default"=>"test"
            ],
            [
                "name" => "",
                "description"=>"",
                "link" => "testInvalidUrl",
                "default"=>"test"
            ],
        ];

        $errorKeys = ['name','description','link','default'];

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
                $this->assertContains($attribute, $errorKeys,"Step ".$key.". The errors object doesn't contain the attribute : ".$attribute);
            }
            $response->assertStatus(422);
        });
        $user->delete();
    }

    /**
     * The test of Create Gateway
     *
     * @return void
     */
    public function testStoreGateway()
    {
        $user = factory(User::class)->create();
        $faker = Factory::create();

        $data = [
            "name" => $faker->name,
            "description"=>$faker->text(100),
            "link" => $faker->url,
            "default"=>$faker->boolean
        ];
        $response = $this
            ->actingAs($user)
            ->post(url('api/gateways'),
                $data,
                ['Authorization' => 'Bearer '.$user->createToken($faker->text(5))->accessToken, 'Accept' => 'application/json'])
            ->assertStatus(200);

        $res_object = json_decode($response->content());

        /* the assert for empty response data */

        $this->assertNotEmpty($res_object, "The response data is empty!");

        $this->assertObjectHasAttribute("success",$res_object, "Response doesn't contain the success attribute!");
        $this->assertTrue($res_object->success, "The success must be true!");
        $this->assertObjectHasAttribute("Gateway",$res_object,"Response doesn't contain the Gateway Object!");

        $this->assertDatabaseHas('gateways', $data);

        $gateway = Gateway::find($res_object->Gateway->id)->delete();

        $user->delete();
    }


    /**
     * The test of Update Gateway
     *
     * @return void
     */
    public function testUpdate()
    {
        $user = factory(User::class)->create();
        $gateway = factory(Gateway::class)->create();
        $faker = Factory::create();

        $dataToUpdate = [
            "name" => $faker->name,
            "description"=>$faker->text(100),
            "link" => $faker->url,
            "default"=>$faker->boolean
        ];

        $response = $this
            ->actingAs($user)
            ->put(url('api/gateways/'.$gateway->id),
                $dataToUpdate,
                ['Authorization' => 'Bearer '.$user->createToken($faker->text(5))->accessToken, 'Accept' => 'application/json'])
            ->assertStatus(200);

        $res_object = json_decode($response->content());

        /* the assert for empty response data */
        $this->assertNotEmpty($res_object, "The response data is empty!");

        $this->assertObjectHasAttribute("success",$res_object, "Response doesn't contain the success attribute!");
        $this->assertTrue($res_object->success, "The success must be true!");
        $this->assertObjectHasAttribute("Gateway",$res_object,"Response doesn't contain the Gateway Object!");

        $dataToUpdate['id'] = $gateway->id;

        $this->assertDatabaseHas('gateways', $dataToUpdate);

        $gateway->delete();
        $user->delete();
    }

    /**
     * A test destroy Gateways
     *
     * @return void
     */
    public function testDestroyGateway()
    {
        $faker = Factory::create();
        $user = factory(User::class)->create();
        $gateway = factory(Gateway::class)->create();
        $response = $this
            ->actingAs($user)
            ->delete(url('api/gateways/'.$gateway->id),[],['Authorization' => 'Bearer '.$user->createToken($faker->text(5))->accessToken, 'Accept' => 'application/json']);

        $res_object = json_decode($response->content());

        /* the assert for empty response data */
        $this->assertNotEmpty($res_object, "The response data is empty");

        $this->assertObjectHasAttribute("success",$res_object, "Response doesn't contain the success attribute!");
        $this->assertTrue($res_object->success, "The success must be true!");
        $this->assertObjectHasAttribute("id",$res_object,"Response doesn't contain the Id!");

        $this->assertDatabaseMissing('gateways', ["id"=>$gateway->id]);

        $user->delete();
    }
}
