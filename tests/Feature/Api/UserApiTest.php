<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test for creating a user via the POST /api/users endpoint.
     *
     * @return void
     */
    public function test_create_user()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123', 
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'message',  
                'user' => [ 
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $data['email'],
        ]);
    }

     /**
     * Test for getting the user data via the GET /api/users/{id} endpoint.
     *
     * @return void
     */
    public function test_get_user()
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJson([
                     'id' => $user->id,
                     'name' => $user->name,
                     'email' => $user->email,
                 ]);
    }
}
