<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'online',
            'service' => 'ProviEmplea API',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
