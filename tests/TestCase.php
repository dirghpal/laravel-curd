<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function createApiUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    protected function createAdminApiUser(array $attributes = []): User
    {
        return $this->createApiUser(array_merge(['role' => 'admin'], $attributes));
    }

    protected function apiHeadersFor(User $user, ?string $token = null): array
    {
        return ['Authorization' => 'Bearer '.($token ?? $user->createApiToken())];
    }
}
