<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_posts(): void
    {
        $user = $this->createAdminApiUser();

        Post::create(['title' => 'First Post', 'body' => 'First body', 'published_at' => now()]);
        Post::create(['title' => 'Second Post', 'body' => 'Second body', 'published_at' => now()]);

        $response = $this->getJson('/api/v1/posts', $this->apiHeadersFor($user));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_post(): void
    {
        $payload = [
            'title' => 'New Post',
            'body' => 'A professional body content.',
            'published_at' => now()->toISOString(),
        ];

        $user = $this->createAdminApiUser();

        $response = $this->postJson('/api/v1/posts', $payload, $this->apiHeadersFor($user));

        $response->assertStatus(201)
            ->assertJson([ 'status' => 'success', 'message' => 'Post created successfully.' ])
            ->assertJsonPath('data.title', 'New Post');

        $this->assertDatabaseHas('posts', [
            'title' => 'New Post',
            'body' => 'A professional body content.',
        ]);
    }

    public function test_can_show_a_post(): void
    {
        $user = $this->createAdminApiUser();
        $post = Post::create(['title' => 'Visible Post', 'body' => 'Visible body']);

        $this->getJson("/api/v1/posts/{$post->id}", $this->apiHeadersFor($user))
            ->assertOk()
            ->assertJsonPath('data.title', 'Visible Post');
    }

    public function test_can_update_post(): void
    {
        $post = Post::create([ 'title' => 'Old Title', 'body' => 'Old body', 'published_at' => now() ]);

        $user = $this->createAdminApiUser();

        $response = $this->putJson("/api/v1/posts/{$post->id}", [
            'title' => 'Updated Title',
            'body' => 'Updated body',
            'published_at' => now()->toISOString(),
        ], $this->apiHeadersFor($user));

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('posts', ['title' => 'Updated Title']);
    }

    public function test_can_delete_post(): void
    {
        $post = Post::create([ 'title' => 'To Delete', 'body' => 'Delete body', 'published_at' => now() ]);

        $user = $this->createAdminApiUser();

        $response = $this->deleteJson("/api/v1/posts/{$post->id}", [], $this->apiHeadersFor($user));

        $response->assertStatus(204);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_returns_validation_error_for_missing_post_fields(): void
    {
        $user = $this->createAdminApiUser();

        $response = $this->postJson('/api/v1/posts', [], $this->apiHeadersFor($user));

        $response->assertStatus(422)
            ->assertJson([ 'status' => 'error', 'message' => 'Please correct the highlighted fields and try again.' ])
            ->assertJsonStructure(['errors']);
    }
}
