<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CleanExpiredData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sima:clean-expired-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired soft-deleted records, sessions, completed maintenance requests, and orphaned approval logs.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting SIMA expired data cleanup...');

        DB::transaction(function () {
            $thirtyDaysAgo = now()->subDays(30);

            // 1. Clean up soft-deleted notifications older than 30 days
            $deletedNotificationsCount = Notification::onlyTrashed()
                ->where('deleted_at', '<', $thirtyDaysAgo)
                ->forceDelete();
            $this->info("Permanently deleted {$deletedNotificationsCount} expired notifications.");

            // 2. Clean up expired user sessions (last activity older than 30 days)
            $deletedSessionsCount = UserSession::where('last_activity', '<', $thirtyDaysAgo)->delete();
            $this->info("Deleted {$deletedSessionsCount} expired user sessions.");

            // 3. Clean up completed maintenance requests older than 30 days and their logs
            $completedMaintenanceIds = MaintenanceRequest::where('status', 'done')
                ->where('updated_at', '<', $thirtyDaysAgo)
                ->pluck('id')
                ->toArray();

            $deletedMaintenanceCount = 0;
            $deletedMaintenanceLogsCount = 0;

            if (!empty($completedMaintenanceIds)) {
                // Delete approval logs using raw DB query to bypass Eloquent delete block
                $deletedMaintenanceLogsCount = DB::table('approval_logs')
                    ->where('approvable_type', 'App\Models\MaintenanceRequest')
                    ->whereIn('approvable_id', $completedMaintenanceIds)
                    ->delete();

                // Delete maintenance requests
                $deletedMaintenanceCount = MaintenanceRequest::whereIn('id', $completedMaintenanceIds)->delete();
            }
            $this->info("Deleted {$deletedMaintenanceCount} completed maintenance requests and {$deletedMaintenanceLogsCount} associated logs.");

            // 4. Clean up orphaned approval logs for deleted items (where parent item no longer exists)
            $orphanedItemLogsCount = DB::table('approval_logs')
                ->where('approvable_type', 'App\Models\Item')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('items')
                        ->whereColumn('items.id', 'approval_logs.approvable_id');
                })
                ->where('created_at', '<', $thirtyDaysAgo)
                ->delete();
            $this->info("Deleted {$orphanedItemLogsCount} orphaned item approval logs.");

            // 5. Clean up orphaned approval logs for deleted maintenance requests
            $orphanedMaintenanceLogsCount = DB::table('approval_logs')
                ->where('approvable_type', 'App\Models\MaintenanceRequest')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('maintenance_requests')
                        ->whereColumn('maintenance_requests.id', 'approval_logs.approvable_id');
                })
                ->where('created_at', '<', $thirtyDaysAgo)
                ->delete();
            $this->info("Deleted {$orphanedMaintenanceLogsCount} orphaned maintenance approval logs.");

            // 6. Clean up soft-deleted users (older than 30 days) with re-assignment
            $expiredUsers = User::onlyTrashed()
                ->where('deleted_at', '<', $thirtyDaysAgo)
                ->get();

            $deletedUsersCount = 0;

            if ($expiredUsers->isNotEmpty()) {
                // Find or create dummy system user
                $systemUser = User::where('username', 'system_deleted_user')->first();
                if (!$systemUser) {
                    $adminRole = Role::where('name', 'admin')->first();
                    $systemUser = User::create([
                        'uuid' => Str::uuid()->toString(),
                        'role_id' => $adminRole->id ?? 1,
                        'email' => 'deleted_user@sima.system',
                        'username' => 'system_deleted_user',
                        'password' => bcrypt(Str::random(16)),
                        'is_active' => false,
                    ]);
                }

                foreach ($expiredUsers as $user) {
                    // Re-assign items
                    Item::where('user_id', $user->id)->update(['user_id' => $systemUser->id]);

                    // Re-assign maintenance requests
                    MaintenanceRequest::where('requester_id', $user->id)->update(['requester_id' => $systemUser->id]);

                    // Permanently delete user session records
                    UserSession::where('user_id', $user->id)->delete();

                    // Force delete the user
                    $user->forceDelete();
                    $deletedUsersCount++;
                }
            }
            $this->info("Permanently deleted {$deletedUsersCount} expired soft-deleted users (re-assigned relations to system_deleted_user).");
        });

        $this->info('SIMA expired data cleanup completed successfully.');
    }
}
