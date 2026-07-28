<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiPostInteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_comment_on_a_post_and_delete_their_comment(): void
    {
        $user = $this->createApiUser();
        $post = Post::create(['title' => 'Post', 'body' => 'Body']);
        $headers = $this->apiHeadersFor($user);

        $response = $this->postJson("/api/v1/posts/{$post->id}/comments", ['body' => 'Great post!'], $headers)
            ->assertCreated()
            ->assertJsonPath('data.body', 'Great post!');

        $commentId = $response->json('data.id');

        $this->getJson("/api/v1/posts/{$post->id}/comments", $headers)
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->deleteJson("/api/v1/comments/{$commentId}", [], $headers)->assertNoContent();
    }

    public function test_user_can_like_and_unlike_a_post_once(): void
    {
        $user = $this->createApiUser();
        $post = Post::create(['title' => 'Post', 'body' => 'Body']);
        $headers = $this->apiHeadersFor($user);

        $this->postJson("/api/v1/posts/{$post->id}/likes", [], $headers)
            ->assertOk()
            ->assertJsonPath('data.likes_count', 1);

        $this->postJson("/api/v1/posts/{$post->id}/likes", [], $headers)
            ->assertOk()
            ->assertJsonPath('data.likes_count', 1);

        $this->deleteJson("/api/v1/posts/{$post->id}/likes", [], $headers)
            ->assertOk()
            ->assertJsonPath('data.likes_count', 0);
    }

    public function test_other_user_cannot_delete_someone_elses_comment(): void
    {
        $author = $this->createApiUser();
        $otherUser = $this->createApiUser();
        $post = Post::create(['title' => 'Post', 'body' => 'Body']);

        $response = $this->postJson("/api/v1/posts/{$post->id}/comments", ['body' => 'Private comment'], $this->apiHeadersFor($author));

        $this->app['auth']->forgetGuards();

        $this->deleteJson('/api/v1/comments/'.$response->json('data.id'), [], $this->apiHeadersFor($otherUser))
            ->assertForbidden();
    }
}
