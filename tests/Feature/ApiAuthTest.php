<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com', 'password' => 'password']);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'message', 'data' => ['token'], 'meta'])
            ->assertJson(['status' => 'success', 'message' => 'Authentication successful.']);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_can_register_and_receive_a_bearer_token(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'device_name' => 'Postman',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.email', 'new@example.com')
            ->assertJsonStructure(['data' => ['token']]);

        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    }

    public function test_cannot_access_protected_routes_without_token(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(401);
    }

    public function test_can_retrieve_authenticated_user(): void
    {
        $user = $this->createApiUser(['email' => 'user2@example.com', 'password' => 'password']);

        $response = $this->getJson('/api/v1/user', $this->apiHeadersFor($user));

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'user2@example.com')
            ->assertJson(['status' => 'success', 'message' => 'Authenticated user retrieved successfully.']);
    }

    public function test_can_logout(): void
    {
        $user = $this->createApiUser(['email' => 'user3@example.com', 'password' => 'password']);
        $token = $user->createApiToken();

        $response = $this->postJson('/api/v1/logout', [], $this->apiHeadersFor($user, $token));

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Successfully logged out.']);

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->getJson('/api/v1/products', $this->apiHeadersFor($user, $token))
            ->assertStatus(401);
    }

    public function test_user_can_reset_their_password(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'reset@example.com']);

        $this->postJson('/api/v1/forgot-password', ['email' => $user->email])
            ->assertOk();

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });

        $this->postJson('/api/v1/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])->assertOk();

        $this->assertTrue(Hash::check('new-password123', $user->fresh()->password));
    }

    public function test_user_can_view_and_revoke_another_device_token(): void
    {
        $user = $this->createApiUser();
        $phoneToken = $user->createToken('Phone');
        $laptopToken = $user->createToken('Laptop');

        $this->getJson('/api/v1/tokens', ['Authorization' => 'Bearer '.$laptopToken->plainTextToken])
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['device_name' => 'Laptop']);

        $this->deleteJson('/api/v1/tokens/'.$phoneToken->accessToken->id, [], [
            'Authorization' => 'Bearer '.$laptopToken->plainTextToken,
        ])->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $phoneToken->accessToken->id]);
    }

    public function test_user_can_request_and_complete_email_verification(): void
    {
        Notification::fake();
        $user = $this->createApiUser(['email_verified_at' => null]);

        $this->postJson('/api/v1/email/verification-notification', [], $this->apiHeadersFor($user))
            ->assertOk();

        Notification::assertSentTo($user, VerifyEmail::class);

        $url = URL::temporarySignedRoute('api.verification.verify', now()->addMinutes(30), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->getJson($url)->assertOk();
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_unverified_user_cannot_access_verified_resources(): void
    {
        $user = $this->createApiUser(['email_verified_at' => null]);

        $this->getJson('/api/v1/products', $this->apiHeadersFor($user))
            ->assertForbidden();
    }

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        $payload = ['email' => 'missing@example.com', 'password' => 'wrong-password'];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/v1/login', $payload)->assertUnauthorized();
        }

        $this->postJson('/api/v1/login', $payload)->assertStatus(429);
    }
}
