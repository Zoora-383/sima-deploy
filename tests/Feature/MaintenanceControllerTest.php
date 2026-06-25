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

class MaintenanceControllerTest extends TestCase
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

    public function test_cannot_create_maintenance_request_with_draft_item_via_api(): void
    {
        $item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'user_id' => $this->adminUser->id,
            'code_item' => 'LOG-ELE-001',
            'name' => 'Laptop Dell',
            'type' => 'logistic',
            'status' => 'draft', // not active
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/maintenance', [
                'item_id' => $item->uuid,
                'title' => 'Fix Keyboard',
                'priority' => 'medium',
                'type' => 'korektif',
                'description' => 'Keyboard keys are not working',
                'target_completion_expectations' => now()->addDays(5)->toDateString(),
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Hanya item dengan status active yang dapat diajukan untuk pemeliharaan.',
        ]);
    }

    public function test_can_create_maintenance_request_with_iso_datetime_format(): void
    {
        $item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'user_id' => $this->adminUser->id,
            'code_item' => 'LOG-ELE-002',
            'name' => 'Laptop HP',
            'type' => 'logistic',
            'status' => 'active',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/maintenance', [
                'item_id' => $item->uuid,
                'title' => 'Fix Screen Screen',
                'priority' => 'medium',
                'type' => 'korektif',
                'description' => 'Screen is dead',
                'target_completion_expectations' => '2026-08-30T13:57:05.895Z', // ISO 8601 format
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('maintenance_requests', [
            'item_id' => $item->id,
            'target_completion_expectations' => '2026-08-30 13:57:05', // Stored as YYYY-MM-DD HH:MM:SS
        ]);
    }
}
