<?php

namespace App\Http\Helpers;

use Illuminate\Http\JsonResponse;

/**
 * Consistent JSON responses for the API.
 */
class ApiResponse
{
    public static function success(mixed $data = null, int $status = 200): JsonResponse
    {
        $body = ['success' => true];
        if ($data !== null) {
            $body['data'] = $data;
        }

        return response()->json($body, $status);
    }

    public static function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $body = [
            'success' => false,
            'message' => $message,
        ];
        if ($errors !== []) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }
}
