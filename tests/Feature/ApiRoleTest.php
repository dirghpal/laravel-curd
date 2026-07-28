<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_cannot_create_a_product(): void
    {
        $user = $this->createApiUser();

        $this->postJson('/api/v1/products', [
            'name' => 'Not allowed',
            'price' => 10,
        ], $this->apiHeadersFor($user))
            ->assertForbidden()
            ->assertJsonPath('message', 'You do not have permission to perform this action.');
    }

    public function test_admin_can_create_a_product(): void
    {
        $admin = $this->createAdminApiUser();

        $this->postJson('/api/v1/products', [
            'name' => 'Admin product',
            'price' => 10,
        ], $this->apiHeadersFor($admin))
            ->assertCreated();
    }
}
