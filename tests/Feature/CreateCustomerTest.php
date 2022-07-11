<?php

namespace Tests\Feature;

use Tests\TestCase;

class CreateCustomerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function testRequiredFieldsForRegistration()
    {

        $data = [
            "firstname" => "a3fb6",
            "lastname" => "Jessica Smith",
            "email" => "solomon.ahamba@botosoft.com",
            "phone_number" => "09034426192",
            "password" => "password",
            "password_confirmation" => "password",
        ];

        $this->json('POST', 'api/v1/customers/register', $data, ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertSee("message");
    }

    public function test_example()
    {

        $data = [
            "firstname" => "a3fb6",
            "lastname" => "Jessica Smith",
            "email" => "solomon.ahaba@botosoft.com",
            "phone_number" => "0903432692",
            "password" => "password",
            "password_confirmation" => "password",
        ];

        $this->json('POST', 'api/v1/customers/register', $data, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJsonStructure([
                "customers" => [
                    'id',
                    'firstname',
                    'lastname',
                    'email',
                    'created_at',
                    'updated_at',
                ],
                "message",
            ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
