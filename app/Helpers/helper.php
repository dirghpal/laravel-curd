<?php

use Illuminate\Http\JsonResponse;

if (! function_exists('api_response')) {
   
    function api_response(mixed $data = null, string $message = 'Success', int $status = 200, array $meta = [])
    {
        $payload = [
            'status' => $status >= 200 && $status < 300 ? 'success' : 'error',
            'message' => $message,
            'data' => $data,
        ];

        if (! empty($meta)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }
}

if (! function_exists('api_error')) {
    
    function api_error(string $message = 'An error occurred.', int $status = 400, array $errors = [])
    {
        $payload = [
            'status' => 'error',
            'message' => $message,
        ];

        if (! empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}

if (! function_exists('api_validation_error')) {
    
    function api_validation_error(array $errors): JsonResponse
    {
        return api_error('Please correct the highlighted fields and try again.', 422, $errors);
    }
}
