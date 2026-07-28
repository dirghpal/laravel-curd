<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    protected function respondSuccess(mixed $data = null, string $message = 'Success', int $status = 200, array $meta = []): \Illuminate\Http\JsonResponse
    {
        return api_response($data, $message, $status, $meta);
    }

    protected function respondError(string $message = 'An error occurred.', int $status = 400, array $errors = []): \Illuminate\Http\JsonResponse
    {
        return api_error($message, $status, $errors);
    }

    protected function validateApi(Request $request, array $rules): array
    {
        return $request->validate($rules);
    }
}
