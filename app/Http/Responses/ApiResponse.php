<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ApiResponse
{
    /**
     * Format sukses seragam.
     *
     * @param  array<string, mixed>  $data
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success.',
        int $statusCode = 200
    ): JsonResponse {
        $body = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $body['data'] = $data;
        }

        return response()->json($body, $statusCode);
    }

    /**
     * Format error seragam.
     *
     * @param  array<string, array<int, string>>|null  $errors
     */
    public static function error(
        string $message = 'An error occurred.',
        ?array $errors = null,
        int $statusCode = 400
    ): JsonResponse {
        $body = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null && $errors !== []) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $statusCode);
    }

    /**
     * Format response untuk validation error (422).
     *
     * @param  array<string, array<int, string>>  $errors
     */
    public static function validationError(
        string $message = 'Validation failed.',
        array $errors = []
    ): JsonResponse {
        return self::error($message, $errors, 422);
    }

    /**
     * Ubah ValidationException ke format API seragam.
     */
    public static function fromValidationException(ValidationException $e): JsonResponse
    {
        $message = $e->getMessage();
        if ($message === 'The given data was invalid.' || $message === '') {
            $message = 'Validation failed. Check the fields that are wrong.';
        }

        return self::validationError($message, $e->errors());
    }
}
