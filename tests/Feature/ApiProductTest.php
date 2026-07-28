<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products(): void
    {
        $user = $this->createAdminApiUser();

        Product::create(['name' => 'Item One', 'price' => 16.50, 'description' => 'Test item one', 'stock' => 5]);
        Product::create(['name' => 'Item Two', 'price' => 23.00, 'description' => 'Test item two', 'stock' => 8]);

        $response = $this->getJson('/api/v1/products', $this->apiHeadersFor($user));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_product(): void
    {
        $payload = [
            'name' => 'Professional Widget',
            'price' => 29.99,
            'description' => 'A polished product item.',
            'stock' => 12,
        ];

        $user = $this->createAdminApiUser();

        $response = $this->postJson('/api/v1/products', $payload, $this->apiHeadersFor($user));

        $response->assertStatus(201)
            ->assertJson([ 'status' => 'success', 'message' => 'Product created successfully.' ])
            ->assertJsonPath('data.name', 'Professional Widget');

        $this->assertDatabaseHas('products', [ 'name' => 'Professional Widget', 'stock' => 12 ]);
    }

    public function test_can_show_a_product(): void
    {
        $user = $this->createAdminApiUser();
        $product = Product::create(['name' => 'Visible Widget', 'price' => 20, 'stock' => 1]);

        $this->getJson("/api/v1/products/{$product->id}", $this->apiHeadersFor($user))
            ->assertOk()
            ->assertJsonPath('data.name', 'Visible Widget');
    }

    public function test_can_update_product(): void
    {
        $product = Product::create(['name' => 'Old Widget', 'price' => 10.00, 'description' => 'Old widget', 'stock' => 2]);

        $user = $this->createAdminApiUser();

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Updated Widget',
            'price' => 14.50,
            'description' => 'Updated widget description',
            'stock' => 10,
        ], $this->apiHeadersFor($user));

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Widget');

        $this->assertDatabaseHas('products', ['name' => 'Updated Widget', 'stock' => 10]);
    }

    public function test_can_delete_product(): void
    {
        $product = Product::create(['name' => 'Delete Widget', 'price' => 8.00, 'description' => 'Delete item', 'stock' => 1]);

        $user = $this->createAdminApiUser();

        $response = $this->deleteJson("/api/v1/products/{$product->id}", [], $this->apiHeadersFor($user));

        $response->assertStatus(204);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_returns_validation_error_for_invalid_product_data(): void
    {
        $user = $this->createAdminApiUser();

        $response = $this->postJson('/api/v1/products', ['name' => '', 'price' => 'invalid'], $this->apiHeadersFor($user));

        $response->assertStatus(422)
            ->assertJson([ 'status' => 'error', 'message' => 'Please correct the highlighted fields and try again.' ])
            ->assertJsonStructure(['errors']);
    }
}
