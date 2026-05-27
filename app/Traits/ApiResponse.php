<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * success response method.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        $response = [
            'status'  => 'success',
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * error response method.
     *
     * @param string $message
     * @param int $code
     * @param mixed $errors
     * @return JsonResponse
     */
    public function errorResponse(string $message = 'Error', int $code = 500, $errors = null): JsonResponse
    {
        $response = [
            'status'  => 'error',
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
