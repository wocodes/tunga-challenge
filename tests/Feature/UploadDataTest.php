<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UploadDataTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testThatUserCanUploadFileSuccessfully()
    {
        $response = $this->post('/');

        $response->assertStatus(200);
    }
}
