<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\UserSession;
use App\Models\ItemCategory;
use App\Models\Item;
use App\Models\MaintenanceRequest;
use App\Models\SPK;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class SpkPdfExportTest extends TestCase
{
    use RefreshDatabase;

    protected $adminToken;
    protected $adminUser;
    protected $category;
    protected $item;
    protected $maintenance;
    protected $spk;

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
        
        $this->item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'user_id' => $this->adminUser->id,
            'code_item' => 'LOG-ELE-001',
            'name' => 'Laptop Dell',
            'type' => 'logistic',
            'status' => 'active',
        ]);

        $this->maintenance = MaintenanceRequest::create([
            'uuid' => Str::uuid()->toString(),
            'nomor_pengajuan' => 'MNT-2026-0001',
            'item_id' => $this->item->id,
            'requester_id' => $this->adminUser->id,
            'title' => 'Fix Screen',
            'priority' => 'high',
            'type' => 'korektif',
            'description' => 'Screen is flickering',
            'target_completion_expectations' => now()->addDays(5)->toDateString(),
            'status' => 'pending_pust',
        ]);

        $this->spk = SPK::create([
            'uuid' => Str::uuid()->toString(),
            'maintenance_id' => $this->maintenance->id,
            'nomor_spk' => 'SPK-2026-0001',
            'tanggal_mulai_efektif' => now()->toDateString(),
            'tanggal_selesai_target' => now()->addDays(5)->toDateString(),
            'pagu_anggaran_disetujui' => 1500000.00,
        ]);
    }

    public function test_can_export_spk_pdf_successfully(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->get('/api/v1/spk/' . $this->spk->uuid . '/pdf');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'attachment; filename="spk-' . $this->spk->uuid . '.pdf"');
    }

    public function test_cannot_export_non_existent_spk_pdf(): void
    {
        $nonExistentUuid = Str::uuid()->toString();
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->get('/api/v1/spk/' . $nonExistentUuid . '/pdf');

        $response->assertStatus(404);
        $response->assertJson([
            'status' => 'error',
            'message' => 'SPK tidak ditemukan.',
        ]);
    }
}
