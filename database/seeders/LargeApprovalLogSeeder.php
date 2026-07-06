<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LargeApprovalLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Safety guard: block execution in production environment
        if (app()->environment('production')) {
            $this->command->error('WARNING: Seeder ini sangat besar dan dilarang keras dijalankan di server produksi!');
            return;
        }

        // 1. Optimize environment resources
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        // 2. Disable query logging to prevent memory leaks
        DB::connection()->disableQueryLog();

        // 3. Retrieve existing User and ItemCategory IDs
        $userIds = DB::table('users')->pluck('id')->toArray();
        $categoryIds = DB::table('item_categories')->pluck('id')->toArray();

        if (empty($userIds) || empty($categoryIds)) {
            $this->command->error('Please run DatabaseSeeder first to ensure users and categories exist.');
            return;
        }

        $totalRows = 500000;
        $chunkSize = 1000;
        $totalChunks = $totalRows / $chunkSize;

        $types = ['logistic', 'non-logistic', 'service'];
        $statuses = ['draft', 'pending_kasi', 'pending_pust', 'revision', 'active', 'maintenance', 'disposed'];

        $this->command->info("Starting seed of {$totalRows} items and {$totalRows} approval logs...");

        $startTime = microtime(true);

        for ($chunk = 1; $chunk <= $totalChunks; $chunk++) {
            $maxId = DB::table('items')->max('id') ?? 0;
            $itemsData = [];
            $now = now()->toDateTimeString();

            // Prepare item chunk
            for ($i = 0; $i < $chunkSize; $i++) {
                $itemIndex = $maxId + $i + 1;
                $itemUuid = $this->generateUuid();
                $codeItem = 'ITEM-' . str_pad($itemIndex, 8, '0', STR_PAD_LEFT);

                $itemsData[] = [
                    'uuid' => $itemUuid,
                    'user_id' => $userIds[array_rand($userIds)],
                    'category_id' => $categoryIds[array_rand($categoryIds)],
                    'code_item' => $codeItem,
                    'name' => 'Aset A ' . $itemIndex,
                    'type' => $types[array_rand($types)],
                    'status' => $statuses[array_rand($statuses)],
                    'units' => rand(1, 100),
                    'image_item' => null,
                    'location' => 'Gedung ' . rand(1, 10) . ', Lantai ' . rand(1, 5),
                    'description' => 'Deskripsi untuk barang ' . $itemIndex,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Perform bulk insert of items
            DB::table('items')->insert($itemsData);

            // Fetch inserted item IDs map via UUIDs (bulletproof way to link morphic relationships)
            $uuids = array_column($itemsData, 'uuid');
            $insertedItems = DB::table('items')
                ->whereIn('uuid', $uuids)
                ->select('id', 'uuid')
                ->get()
                ->pluck('id', 'uuid');

            // Build approval logs chunk
            $logsData = [];
            foreach ($itemsData as $item) {
                $itemId = $insertedItems[$item['uuid']] ?? null;
                if ($itemId) {
                    $logsData[] = [
                        'uuid' => $this->generateUuid(),
                        'approvable_type' => 'App\Models\Item',
                        'approvable_id' => $itemId,
                        'user_id' => $userIds[array_rand($userIds)],
                        'status_from' => 'draft',
                        'status_to' => $item['status'],
                        'note' => 'Inisiasi log sistem untuk data barang baru.',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // Perform bulk insert of approval logs
            DB::table('approval_logs')->insert($logsData);

            // Free memory explicitly
            unset($itemsData);
            unset($uuids);
            unset($insertedItems);
            unset($logsData);

            // Log progress
            $processed = $chunk * $chunkSize;
            $elapsed = microtime(true) - $startTime;
            $percent = ($processed / $totalRows) * 100;
            $mem = round(memory_get_usage(true) / 1024 / 1024, 2);
            $this->command->info(sprintf(
                "Processed %d/%d (%.1f%%) in %.2fs. Memory usage: %.2f MB",
                $processed,
                $totalRows,
                $percent,
                $elapsed,
                $mem
            ));
        }

        $this->command->info("Finished! Total time: " . round(microtime(true) - $startTime, 2) . "s");
    }

    /**
     * Fast UUIDv4 generator.
     */
    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100 (v4)
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set variant bits
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
