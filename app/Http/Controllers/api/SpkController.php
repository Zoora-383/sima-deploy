<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Spk\SpkStoreRequest;
use App\Http\Requests\Spk\SpkUpdateRequest;
use App\Http\Resources\SPKIndexResource;
use App\Http\Resources\SPKResource;
use App\Services\SPKService;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
// use Illuminate\Http\Request;

class SpkController extends Controller
{
    protected $SPKService;

    public function __construct(SPKService $SPKService)
    {
        $this->SPKService = $SPKService;
    }

    public function store(SpkStoreRequest $request)
    {
        try {
            $currentUser = auth('api')->user();

            $newSpk = $this->SPKService->addSPK($request->validated(), $currentUser);

            return $this->successResponse(
                new SPKResource($newSpk),
                'SPK berhasil dibuat dan otomatis disetujui.',
                201
            );
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {
            Log::error('SPK Store Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal membuat SPK: ' . $e->getMessage(), 500);
        }
    }

    public function index()
    {
        try {
            $spks = $this->SPKService->getAllSPK();

            return $this->successResponse(SPKIndexResource::collection($spks), 'Get all spk successfully');
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            Log::error('SPK Index Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memuat daftar SPK.');
        }
    }

    public function show(string $uuid)
    {
        try {
            $spk = $this->SPKService->getDetailSpk($uuid);

            return $this->successResponse(new SPKResource($spk), 'Get detail SPK successfully');
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            Log::error('SPK Show Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil data detail SPK.');
        }
    }

    public function update(SpkUpdateRequest $request, string $uuid)
    {
        try {
            $updatedSpk = $this->SPKService->updateSpk($request->validated(), $uuid);

            return $this->successResponse(
                new SPKResource($updatedSpk),
                'Data SPK berhasil diperbarui.'
            );
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            Log::error('SPK Update Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memperbarui data SPK.');
        }
    }

    public function destroy(string $uuid)
    {
        try {
            $spk = $this->SPKService->deleteSpk($uuid);

            return $this->successResponse(null, 'Deleted spk successfully');
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            Log::error('SPK Index Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memuat daftar SPK.');
        }
    }

    public function exportPdf(string $uuid)
    {
        try {
            $pdfStream = $this->SPKService->generateSpkPdf($uuid);

            return response($pdfStream, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="spk-' . $uuid . '.pdf"');
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            Log::error('SPK PDF Export Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengunduh dokumen SPK.', 500);
        }
    }
}
