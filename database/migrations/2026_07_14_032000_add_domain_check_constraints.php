<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE maintenance_request_items ADD CONSTRAINT chk_maintenance_request_items_qty CHECK (qty >= 0)');
            DB::statement('ALTER TABLE maintenance_request_items ADD CONSTRAINT chk_maintenance_request_items_biaya CHECK (estimasi_biaya_satuan >= 0)');
            DB::statement('ALTER TABLE spks ADD CONSTRAINT chk_spks_pagu_anggaran CHECK (pagu_anggaran_disetujui >= 0)');
            DB::statement('ALTER TABLE maintenance_rekaps ADD CONSTRAINT chk_maintenance_rekaps_realisasi CHECK (realisasi_biaya >= 0)');
            
            DB::statement("ALTER TABLE items ADD CONSTRAINT chk_items_status CHECK (status IN ('draft', 'pending_kasi', 'pending_pust', 'active', 'revision', 'disposed'))");
            DB::statement("ALTER TABLE maintenance_requests ADD CONSTRAINT chk_maintenance_status CHECK (status IN ('draft', 'pending_kasi', 'pending_pust', 'in_progress', 'done', 'revision'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE maintenance_request_items DROP CONSTRAINT chk_maintenance_request_items_qty');
            DB::statement('ALTER TABLE maintenance_request_items DROP CONSTRAINT chk_maintenance_request_items_biaya');
            DB::statement('ALTER TABLE spks DROP CONSTRAINT chk_spks_pagu_anggaran');
            DB::statement('ALTER TABLE maintenance_rekaps DROP CONSTRAINT chk_maintenance_rekaps_realisasi');
            
            DB::statement('ALTER TABLE items DROP CONSTRAINT chk_items_status');
            DB::statement('ALTER TABLE maintenance_requests DROP CONSTRAINT chk_maintenance_status');
        }
    }
};
