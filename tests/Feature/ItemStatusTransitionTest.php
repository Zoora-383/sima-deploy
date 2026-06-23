<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Role;
use App\Models\User;
use App\Services\ItemService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ItemStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected ItemService $itemService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->itemService = $this->app->make(ItemService::class);
    }

    public function test_admin_resubmitting_item_revised_by_kel_pust_goes_directly_to_pending_pust(): void
    {
        $adminRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'admin']);
        $pustRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'kel_pust']);

        $adminUser = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $adminRole->id,
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);

        $pustUser = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $pustRole->id,
            'email' => 'pust@test.com',
            'username' => 'pust',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);

        $category = ItemCategory::create(['uuid' => Str::uuid()->toString(), 'name' => 'Electronic']);
        $item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $category->id,
            'user_id' => $adminUser->id,
            'code_item' => 'LOG-ELE-001',
            'name' => 'Laptop Dell',
            'type' => 'logistic',
            'status' => 'revision',
        ]);

        // Record a log showing it was revised by Kel Pust
        $item->approvalLogs()->create([
            'uuid' => Str::uuid()->toString(),
            'user_id' => $pustUser->id,
            'status_from' => 'pending_pust',
            'status_to' => 'revision',
            'note' => 'Revised by Kel Pust for test',
        ]);

        // When Admin resubmits to pending_kasi, it should dynamically transition to pending_pust
        $updated = $this->itemService->updateStatus(
            $item->uuid,
            ['status' => 'pending_kasi', 'note' => 'Resubmitting'],
            $adminUser
        );

        $this->assertEquals('pending_pust', $updated->status);
    }

    public function test_admin_resubmitting_item_revised_by_kasi_goes_to_pending_kasi(): void
    {
        $adminRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'admin']);
        $kasiRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'kasi']);

        $adminUser = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $adminRole->id,
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);

        $kasiUser = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $kasiRole->id,
            'email' => 'kasi@test.com',
            'username' => 'kasi',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);

        $category = ItemCategory::create(['uuid' => Str::uuid()->toString(), 'name' => 'Electronic']);
        $item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $category->id,
            'user_id' => $adminUser->id,
            'code_item' => 'LOG-ELE-001',
            'name' => 'Laptop Dell',
            'type' => 'logistic',
            'status' => 'revision',
        ]);

        // Record a log showing it was revised by Kasi
        $item->approvalLogs()->create([
            'uuid' => Str::uuid()->toString(),
            'user_id' => $kasiUser->id,
            'status_from' => 'pending_kasi',
            'status_to' => 'revision',
            'note' => 'Revised by Kasi for test',
        ]);

        // When Admin resubmits to pending_kasi, it should transition to pending_kasi
        $updated = $this->itemService->updateStatus(
            $item->uuid,
            ['status' => 'pending_kasi', 'note' => 'Resubmitting'],
            $adminUser
        );

        $this->assertEquals('pending_kasi', $updated->status);
    }

    public function test_controller_update_status_validation_invalid_transition_returns_422(): void
    {
        $adminRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'admin']);
        $adminUser = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $adminRole->id,
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);

        $category = ItemCategory::create(['uuid' => Str::uuid()->toString(), 'name' => 'Electronic']);
        $item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $category->id,
            'user_id' => $adminUser->id,
            'code_item' => 'LOG-ELE-001',
            'name' => 'Laptop Dell',
            'type' => 'logistic',
            'status' => 'draft',
        ]);

        $response = $this->withoutMiddleware([
            \App\Http\Middleware\JwtCheckMiddleware::class,
            \App\Http\Middleware\ForcePasswordChangeMiddleware::class,
            \App\Http\Middleware\RoleMiddleware::class
        ])
        ->actingAs($adminUser, 'api')
        ->patchJson("/api/v1/items/{$item->uuid}/status", [
            'status' => 'active',
            'note' => 'Invalid attempt'
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Anda tidak memiliki izin untuk melakukan transisi status ini.'
        ]);
    }

    public function test_controller_update_status_item_not_found_returns_404(): void
    {
        $adminRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'admin']);
        $adminUser = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $adminRole->id,
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);

        $nonExistentUuid = (string) Str::uuid();

        $response = $this->withoutMiddleware([
            \App\Http\Middleware\JwtCheckMiddleware::class,
            \App\Http\Middleware\ForcePasswordChangeMiddleware::class,
            \App\Http\Middleware\RoleMiddleware::class
        ])
        ->actingAs($adminUser, 'api')
        ->patchJson("/api/v1/items/{$nonExistentUuid}/status", [
            'status' => 'pending_kasi',
            'note' => 'Valid transition but invalid uuid'
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Item not found.'
        ]);
    }
}

