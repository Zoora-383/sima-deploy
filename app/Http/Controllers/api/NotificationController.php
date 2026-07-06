<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(): JsonResponse
    {
        try {
            $currentUser = auth('api')->user();
            
            $notifications = Notification::where('user_id', $currentUser->id)
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return $this->successResponse(
                NotificationResource::collection($notifications)->response()->getData(true),
                'Daftar notifikasi berhasil dimuat.'
            );
        } catch (Exception $e) {
            Log::error('Fetch Notifications Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memuat daftar notifikasi.', 500);
        }
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(string $uuid): JsonResponse
    {
        try {
            $currentUser = auth('api')->user();

            $notification = Notification::where('uuid', $uuid)
                ->where('user_id', $currentUser->id)
                ->first();

            if (!$notification) {
                throw new NotFoundHttpException('Notifikasi tidak ditemukan.');
            }

            $notification->update(['read_at' => now()]);

            return $this->successResponse(
                new NotificationResource($notification),
                'Notifikasi ditandai telah dibaca.'
            );
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            Log::error('Mark Notification Read Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal menandai notifikasi.', 500);
        }
    }

    /**
     * Mark all notifications of the user as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $currentUser = auth('api')->user();

            Notification::where('user_id', $currentUser->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return $this->successResponse(
                null,
                'Semua notifikasi ditandai telah dibaca.'
            );
        } catch (Exception $e) {
            Log::error('Mark All Notifications Read Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal menandai semua notifikasi.', 500);
        }
    }

    /**
     * Delete all notifications of the logged-in user (soft delete).
     */
    public function deleteAll(): JsonResponse
    {
        try {
            $currentUser = auth('api')->user();

            Notification::where('user_id', $currentUser->id)->delete();

            return $this->successResponse(
                null,
                'Semua notifikasi berhasil dihapus.'
            );
        } catch (Exception $e) {
            Log::error('Delete All Notifications Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal menghapus semua notifikasi.', 500);
        }
    }

    /**
     * Delete a specific notification (soft delete).
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $currentUser = auth('api')->user();

            $notification = Notification::where('uuid', $uuid)
                ->where('user_id', $currentUser->id)
                ->first();

            if (!$notification) {
                throw new NotFoundHttpException('Notifikasi tidak ditemukan.');
            }

            $notification->delete();

            return $this->successResponse(
                null,
                'Notifikasi berhasil dihapus.'
            );
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            Log::error('Delete Notification Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal menghapus notifikasi.', 500);
        }
    }
}
