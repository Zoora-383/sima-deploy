<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\UserSession;
use App\Models\ItemCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ItemCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected $adminToken;
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Admin User
        $adminRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'admin']);
        $this->adminUser = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $adminRole->id,
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);
        $this->adminToken = JWTAuth::fromUser($this->adminUser);
        
        UserSession::create([
            'user_id' => $this->adminUser->id,
            'jti' => JWTAuth::setToken($this->adminToken)->getPayload()->get('jti'),
            'device_info' => 'PHPUnit',
            'last_activity' => now(),
        ]);
    }

    public function test_admin_can_create_item_category(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/item-category', [
                'name' => 'Electronic',
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Item category created successfully',
        ]);

        $this->assertDatabaseHas('item_categories', [
            'name' => 'Electronic',
        ]);
    }

    public function test_admin_cannot_create_duplicate_item_category(): void
    {
        ItemCategory::create([
            'uuid' => Str::uuid()->toString(),
            'name' => 'Electronic',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/item-category', [
                'name' => 'Electronic',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_admin_can_update_item_category(): void
    {
        $category = ItemCategory::create([
            'uuid' => Str::uuid()->toString(),
            'name' => 'Electronic',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->putJson("/api/v1/item-category/{$category->uuid}", [
                'name' => 'Gadgets',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('item_categories', [
            'uuid' => $category->uuid,
            'name' => 'Gadgets',
        ]);
    }

    public function test_admin_cannot_update_item_category_to_existing_name(): void
    {
        ItemCategory::create([
            'uuid' => Str::uuid()->toString(),
            'name' => 'Electronic',
        ]);

        $category2 = ItemCategory::create([
            'uuid' => Str::uuid()->toString(),
            'name' => 'Furniture',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->putJson("/api/v1/item-category/{$category2->uuid}", [
                'name' => 'Electronic',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_admin_can_update_item_category_to_same_name(): void
    {
        $category = ItemCategory::create([
            'uuid' => Str::uuid()->toString(),
            'name' => 'Electronic',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->putJson("/api/v1/item-category/{$category->uuid}", [
                'name' => 'Electronic',
            ]);

        $response->assertStatus(200);
    }
}
