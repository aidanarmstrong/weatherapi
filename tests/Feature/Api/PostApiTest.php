<?php

namespace Tests\Feature\Api;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

   /**
     * Test to fetch a post by ID.
     *
     * @return void
    */
    public function test_show_post()
    {
        $user = User::factory()->create();

        $post = Post::create([
            'title' => 'My Post Title',
            'content' => 'This is the content of the post',
            'user_id' => $user->id,
        ]);

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJson([
                     'id' => $post->id,
                     'title' => $post->title,
                     'content' => $post->content,
                 ]);
    }

    /**
     * Test to return 404 when post not found.
     *
     * @return void
     */
    public function test_show_post_not_found()
    {
        $response = $this->getJson('/api/posts/0000');

        $response->assertStatus(Response::HTTP_NOT_FOUND)
                 ->assertJson([
                     'error' => 'Post not found',
                 ]);
    }

}
