<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class HealthController extends Controller
{
    #[OA\Get(
        path: '/health',
        operationId: 'healthCheck',
        tags: ['Health'],
        summary: 'Verificar estado del servicio',
        description: 'Endpoint de observabilidad. Verifica que la API está disponible. Tiene un rate limit de 120 solicitudes por minuto por IP y encabezado Cache-Control de 15 segundos para reducir consultas repetidas. Tiempo de respuesta esperado: menor a 200 ms en entorno local con baja carga.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Servicio operativo',
                content: new OA\JsonContent(
                    type: 'object',
                    example: ['status' => 'online', 'service' => 'ProviEmplea API', 'version' => '1.0.0', 'timestamp' => '2026-05-21T10:30:00+00:00']
                )
            ),
            new OA\Response(
                response: 429,
                description: 'Demasiadas solicitudes',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'online',
            'service' => 'ProviEmplea API',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ], 200, ['Cache-Control' => 'public, max-age=15']);
    }
}
