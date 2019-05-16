<?php

namespace Tests\Feature;

use App\Models\Event;
use App\User;
use Faker\Factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventTest extends TestCase
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
            $response = $this
                ->actingAs($user)
                ->post(url('api/events'),
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
     * The test of Create Event
     *
     * @return void
     */
    public function testCreate()
    {
        $user = factory(User::class)->create();
        $faker = Factory::create();

        $response = $this
            ->actingAs($user)
            ->post(url('api/events'),
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
        $this->assertObjectHasAttribute("Event",$res_object,"Response doesn't contain the Event Object!");

        $user->delete();
    }


    /**
     * The test of Update Event
     *
     * @return void
     */
    public function testUpdate()
    {
        $user = factory(User::class)->create();
        $event = factory(Event::class)->create();
        $faker = Factory::create();

        $response = $this
            ->actingAs($user)
            ->put(url('api/events/'.$event->id),
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
        $this->assertObjectHasAttribute("Event",$res_object,"Response doesn't contain the Event Object!");

        $user->delete();
    }

    /**
     * A test destroy Events
     *
     * @return void
     */
    public function testDestroyEvent()
    {
        $faker = Factory::create();
        $user = factory(User::class)->create();
        $event = factory(Event::class)->create();
        $response = $this
            ->actingAs($user)
            ->delete(url('api/events/'.$event->id),[],['Authorization' => 'Bearer '.$user->createToken($faker->text(5))->accessToken, 'Accept' => 'application/json']);

        $res_object = json_decode($response->content());

        /* the assert for empty response data */
        $this->assertNotEmpty($res_object, "The response data is empty");

        $this->assertObjectHasAttribute("success",$res_object, "Response doesn't contain the success attribute!");
        $this->assertTrue($res_object->success, "The success must be true!");
        $this->assertObjectHasAttribute("id",$res_object,"Response doesn't contain the Id!");
        $user->delete();
    }
}
