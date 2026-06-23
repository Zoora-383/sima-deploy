<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\UserSession;
use App\Models\ItemCategory;
use App\Models\Item;
use App\Models\MaintenanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class MaintenanceManipulationTest extends TestCase
{
    use RefreshDatabase;

    protected $adminToken;
    protected $adminUser;
    protected $item;

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

        $category = ItemCategory::create(['uuid' => Str::uuid()->toString(), 'name' => 'Electronic']);
        $this->item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $category->id,
            'user_id' => $this->adminUser->id,
            'code_item' => 'LOG-ELE-001',
            'name' => 'Laptop Dell',
            'type' => 'logistic',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_update_and_delete_maintenance_in_draft_or_rejected_status(): void
    {
        // 1. Create Maintenance in Draft
        $maintenance = MaintenanceRequest::create([
            'uuid' => Str::uuid()->toString(),
            'nomor_pengajuan' => 'REQ-20260623-0001',
            'item_id' => $this->item->id,
            'requester_id' => $this->adminUser->id,
            'title' => 'Original Title',
            'priority' => 'medium',
            'type' => 'korektif',
            'description' => 'Original Description',
            'target_completion_expectations' => now()->addDays(5)->toDateString(),
            'status' => 'draft',
        ]);

        // 2. Update Maintenance (Draft)
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->putJson("/api/v1/maintenance/{$maintenance->uuid}", [
                'title' => 'Updated Title Draft',
                'priority' => 'high',
                'type' => 'korektif',
                'description' => 'Updated Description',
                'target_completion_expectations' => now()->addDays(5)->toDateString(),
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('maintenance_requests', [
            'uuid' => $maintenance->uuid,
            'title' => 'Updated Title Draft',
            'priority' => 'high',
        ]);

        // 3. Update Maintenance (Rejected)
        $maintenance->update(['status' => 'rejected']);
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->putJson("/api/v1/maintenance/{$maintenance->uuid}", [
                'title' => 'Updated Title Rejected',
                'priority' => 'high',
                'type' => 'korektif',
                'description' => 'Updated Description',
                'target_completion_expectations' => now()->addDays(5)->toDateString(),
            ]);

        $response2->assertStatus(200);
        $this->assertDatabaseHas('maintenance_requests', [
            'uuid' => $maintenance->uuid,
            'title' => 'Updated Title Rejected',
        ]);

        // 4. Delete Maintenance (Rejected)
        $response3 = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->deleteJson("/api/v1/maintenance/{$maintenance->uuid}");

        $response3->assertStatus(200);
        $this->assertDatabaseMissing('maintenance_requests', ['uuid' => $maintenance->uuid]);
    }

    public function test_admin_cannot_update_or_delete_maintenance_in_active_validation_stages(): void
    {
        $maintenance = MaintenanceRequest::create([
            'uuid' => Str::uuid()->toString(),
            'nomor_pengajuan' => 'REQ-20260623-0002',
            'item_id' => $this->item->id,
            'requester_id' => $this->adminUser->id,
            'title' => 'Fix Screen',
            'priority' => 'medium',
            'type' => 'korektif',
            'description' => 'Screen is flickering',
            'target_completion_expectations' => now()->addDays(5)->toDateString(),
            'status' => 'pending_kasi', // active validation status
        ]);

        // 1. Try Update (Pending Kasi) -> Should fail
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->putJson("/api/v1/maintenance/{$maintenance->uuid}", [
                'title' => 'Try Change Title',
                'priority' => 'medium',
                'type' => 'korektif',
                'description' => 'Screen is flickering',
                'target_completion_expectations' => now()->addDays(5)->toDateString(),
            ]);

        $response->assertStatus(422); // InvalidArgumentException mapped to 422
        $response->assertJson([
            'status' => 'error',
            'message' => 'Pengajuan tidak dapat diubah karena sedang dalam proses validasi atau pengerjaan (Status saat ini: pending kasi).',
        ]);

        // 2. Try Delete (Pending Kasi) -> Should fail
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->deleteJson("/api/v1/maintenance/{$maintenance->uuid}");

        $response2->assertStatus(422); // InvalidArgumentException mapped to 422
        $response2->assertJson([
            'status' => 'error',
            'message' => 'Pengajuan tidak dapat dihapus karena sedang dalam proses validasi atau pengerjaan (Status saat ini: pending kasi).',
        ]);
    }
}
