<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadDataTest extends TestCase
{

    use RefreshDatabase;
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testThatUserCanUploadFileSuccessfully()
    {
        $data = [];

        for($i=0;$i<10;$i++) {
            $data[] = [
                "name" => $this->faker->name,
                'address' => $this->faker->address,
                'checked' => $this->faker->boolean,
                'description' => $this->faker->sentence,
                'interest' => $this->faker->word,
                'date_of_birth' => $this->faker->date,
                'email' => $this->faker->email,
                'account' => $this->faker->randomNumber(),
                'credit_card' => [
                    "type" => $this->faker->randomElement(["Visa", "Mastercard"]),
                    "number" => $this->faker->randomNumber(),
                    "name" => $this->faker->name,
                    "expiration" => $this->faker->month . "/" . substr($this->faker->year, -2, 2)
                ]
            ];
        }

        $disk = Storage::disk('local');
        $disk->put("public/uploaded_users.json", json_encode($data));

        $response = $this->post('/');

        $response->assertOk();
    }

    //TODO: Write more test cases
}
