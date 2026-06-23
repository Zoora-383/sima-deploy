<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\UserSession;
use App\Models\ItemCategory;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ItemManipulationTest extends TestCase
{
    use RefreshDatabase;

    protected $adminToken;
    protected $adminUser;
    protected $category;

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

        $this->category = ItemCategory::create(['uuid' => Str::uuid()->toString(), 'name' => 'Electronic']);
    }

    public function test_admin_can_edit_item_in_draft_or_revision_or_active_status(): void
    {
        // 1. Test edit in draft
        $item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'user_id' => $this->adminUser->id,
            'code_item' => 'LOG-ELE-001',
            'name' => 'Original Name',
            'type' => 'logistic',
            'status' => 'draft',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->putJson("/api/v1/items/{$item->uuid}", [
                'name' => 'Updated Name Draft',
                'type' => 'logistic',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('items', [
            'uuid' => $item->uuid,
            'name' => 'Updated Name Draft',
            'status' => 'draft',
        ]);

        // 2. Test edit in revision
        $item->update(['status' => 'revision']);
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->putJson("/api/v1/items/{$item->uuid}", [
                'name' => 'Updated Name Revision',
                'type' => 'logistic',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('items', [
            'uuid' => $item->uuid,
            'name' => 'Updated Name Revision',
            'status' => 'revision',
        ]);

        // 3. Test edit in active
        $item->update(['status' => 'active']);
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->putJson("/api/v1/items/{$item->uuid}", [
                'name' => 'Updated Name Active',
                'type' => 'logistic',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('items', [
            'uuid' => $item->uuid,
            'name' => 'Updated Name Active',
            'status' => 'active',
        ]);
    }

    public function test_admin_editing_item_in_pending_kasi_pulls_it_back_to_draft(): void
    {
        $item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'user_id' => $this->adminUser->id,
            'code_item' => 'LOG-ELE-001',
            'name' => 'Original Name',
            'type' => 'logistic',
            'status' => 'pending_kasi',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->putJson("/api/v1/items/{$item->uuid}", [
                'name' => 'Updated Name Pending Kasi',
                'type' => 'logistic',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('items', [
            'uuid' => $item->uuid,
            'name' => 'Updated Name Pending Kasi',
            'status' => 'draft', // Pulled back to draft!
        ]);

        $this->assertDatabaseHas('approval_logs', [
            'status_from' => 'pending_kasi',
            'status_to' => 'draft',
        ]);
    }

    public function test_admin_cannot_edit_item_in_pending_pust_or_maintenance(): void
    {
        // 1. Pending Pust
        $item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'user_id' => $this->adminUser->id,
            'code_item' => 'LOG-ELE-001',
            'name' => 'Original Name',
            'type' => 'logistic',
            'status' => 'pending_pust',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->putJson("/api/v1/items/{$item->uuid}", [
                'name' => 'Try Edit',
                'type' => 'logistic',
            ]);

        $response->assertStatus(500);

        // 2. Maintenance
        $item->update(['status' => 'maintenance']);
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->putJson("/api/v1/items/{$item->uuid}", [
                'name' => 'Try Edit 2',
                'type' => 'logistic',
            ]);

        $response->assertStatus(500);
    }

    public function test_admin_can_delete_item_in_draft_or_revision_and_dispose_in_active(): void
    {
        // 1. Draft
        $item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'user_id' => $this->adminUser->id,
            'code_item' => 'LOG-ELE-001',
            'name' => 'Item Draft',
            'type' => 'logistic',
            'status' => 'draft',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->deleteJson("/api/v1/items/{$item->uuid}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('items', ['uuid' => $item->uuid]);

        // 2. Revision
        $item2 = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'user_id' => $this->adminUser->id,
            'code_item' => 'LOG-ELE-002',
            'name' => 'Item Revision',
            'type' => 'logistic',
            'status' => 'revision',
        ]);

        $response2 = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->deleteJson("/api/v1/items/{$item2->uuid}");

        $response2->assertStatus(200);
        $this->assertDatabaseMissing('items', ['uuid' => $item2->uuid]);

        // 3. Active (Dispose)
        $item3 = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'user_id' => $this->adminUser->id,
            'code_item' => 'LOG-ELE-003',
            'name' => 'Item Active',
            'type' => 'logistic',
            'status' => 'active',
        ]);

        $response3 = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->deleteJson("/api/v1/items/{$item3->uuid}");

        $response3->assertStatus(200);
        $this->assertDatabaseHas('items', [
            'uuid' => $item3->uuid,
            'status' => 'disposed',
        ]);
    }

    public function test_admin_cannot_delete_item_in_pending_kasi_or_pending_pust_or_maintenance(): void
    {
        $item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'user_id' => $this->adminUser->id,
            'code_item' => 'LOG-ELE-001',
            'name' => 'Item Draft',
            'type' => 'logistic',
            'status' => 'pending_kasi',
        ]);

        // 1. Pending Kasi
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->deleteJson("/api/v1/items/{$item->uuid}");

        $response->assertStatus(500);

        // 2. Pending Pust
        $item->update(['status' => 'pending_pust']);
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->deleteJson("/api/v1/items/{$item->uuid}");

        $response->assertStatus(500);

        // 3. Maintenance
        $item->update(['status' => 'maintenance']);
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->deleteJson("/api/v1/items/{$item->uuid}");

        $response->assertStatus(500);
    }
}
