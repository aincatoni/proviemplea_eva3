<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class PersonaController extends Controller
{
    #[OA\Get(
        path: '/personas',
        operationId: 'getPersonas',
        tags: ['Personas'],
        summary: 'Listar personas (CV ciego)',
        description: 'Obtiene talentos activos en formato de CV ciego (sin datos personales identificables). Rate limit: 120 solicitudes por minuto por IP. Se recomienda aplicar filtros para reducir payload y paginar si el volumen crece.',
        parameters: [
            new OA\Parameter(name: 'validado', in: 'query', required: false, description: 'Filtrar por validación', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'nivel_educacional', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['basica', 'media', 'tecnica', 'universitaria', 'postgrado'])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado exitoso',
                content: new OA\JsonContent(ref: '#/components/schemas/PersonaListResponse')
            ),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Persona::where('activo', true);

        if ($request->has('validado')) {
            $query->where('validado', $request->boolean('validado'));
        }
        if ($request->has('nivel_educacional')) {
            $query->where('nivel_educacional', $request->input('nivel_educacional'));
        }

        return $this->successResponse($query->get()->map(fn ($p) => $p->getCvCiego()));
    }

    #[OA\Post(
        path: '/personas',
        operationId: 'createPersona',
        tags: ['Personas'],
        summary: 'Registrar nueva persona/talento',
        description: 'Crea un perfil de talento. El código se genera automáticamente.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/PersonaInput')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Persona creada',
                content: new OA\JsonContent(ref: '#/components/schemas/PersonaResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Errores de validación',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:personas,email',
            'telefono' => 'nullable|string|max:15',
            'resumen' => 'nullable|string',
            'nivel_educacional' => 'nullable|in:basica,media,tecnica,universitaria,postgrado',
            'titulo_carrera' => 'nullable|string',
            'anio_egreso' => 'nullable|integer|min:1950|max:'.date('Y'),
            'anios_experiencia' => 'nullable|integer|min:0',
            'areas_experiencia' => 'nullable|array',
            'competencias' => 'nullable|array',
            'rango_renta' => 'nullable|string',
            'tipo_jornada' => 'nullable|in:completa,part-time,por-horas',
            'modalidad' => 'nullable|in:presencial,remoto,hibrido',
            'cursos' => 'nullable|array',
            'idiomas' => 'nullable|array',
            'portafolio_url' => 'nullable|url',
            'persona_discapacidad' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son válidos.', 422, $validator->errors()->toArray());
        }

        $data = $validator->validated();
        $data['codigo_talento'] = $this->generarCodigoTalento();
        $data['porcentaje_completitud'] = $this->calcularCompletitud($data);

        return $this->successResponse(Persona::create($data), 201);
    }

    #[OA\Get(
        path: '/personas/{persona}',
        operationId: 'getPersona',
        tags: ['Personas'],
        summary: 'Obtener persona por ID',
        description: 'Consulta de lectura con rate limit de 120 solicitudes por minuto por IP.',
        parameters: [
            new OA\Parameter(name: 'persona', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'), example: '550e8400-e29b-41d4-a716-446655440000'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Persona encontrada',
                content: new OA\JsonContent(ref: '#/components/schemas/PersonaResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'No encontrada',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function show(string $persona): JsonResponse
    {
        $model = Persona::find($persona);
        if (! $model) {
            return $this->errorResponse('Persona no encontrada.', 404);
        }

        return $this->successResponse($model);
    }

    #[OA\Put(
        path: '/personas/{persona}',
        operationId: 'updatePersona',
        tags: ['Personas'],
        summary: 'Actualizar persona',
        parameters: [
            new OA\Parameter(name: 'persona', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'), example: '550e8400-e29b-41d4-a716-446655440000'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/PersonaInput')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Persona actualizada',
                content: new OA\JsonContent(ref: '#/components/schemas/PersonaResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'No encontrada',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Errores de validación',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function update(Request $request, string $persona): JsonResponse
    {
        $model = Persona::find($persona);
        if (! $model) {
            return $this->errorResponse('Persona no encontrada.', 404);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'sometimes|email|unique:personas,email,'.$model->id,
            'telefono' => 'nullable|string|max:15',
            'resumen' => 'nullable|string',
            'nivel_educacional' => 'nullable|in:basica,media,tecnica,universitaria,postgrado',
            'titulo_carrera' => 'nullable|string',
            'anio_egreso' => 'nullable|integer|min:1950|max:'.date('Y'),
            'anios_experiencia' => 'nullable|integer|min:0',
            'areas_experiencia' => 'nullable|array',
            'competencias' => 'nullable|array',
            'rango_renta' => 'nullable|string',
            'tipo_jornada' => 'nullable|in:completa,part-time,por-horas',
            'modalidad' => 'nullable|in:presencial,remoto,hibrido',
            'cursos' => 'nullable|array',
            'idiomas' => 'nullable|array',
            'portafolio_url' => 'nullable|url',
            'persona_discapacidad' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son válidos.', 422, $validator->errors()->toArray());
        }

        $data = $validator->validated();
        $data['porcentaje_completitud'] = $this->calcularCompletitud(array_merge($model->toArray(), $data));
        $model->update($data);

        return $this->successResponse($model->fresh());
    }

    #[OA\Patch(
        path: '/personas/{persona}/validar',
        operationId: 'validarPersona',
        tags: ['Personas'],
        summary: 'Validar persona (solo administración)',
        description: 'Marca a una persona como validada para que aparezca en la vitrina. Rate limit: 30 solicitudes por minuto por IP.',
        parameters: [
            new OA\Parameter(name: 'persona', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'), example: '550e8400-e29b-41d4-a716-446655440000'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Persona validada',
                content: new OA\JsonContent(ref: '#/components/schemas/PersonaValidationResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'No encontrada',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function validar(string $persona): JsonResponse
    {
        $model = Persona::find($persona);
        if (! $model) {
            return $this->errorResponse('Persona no encontrada.', 404);
        }
        $model->update(['validado' => true]);

        return $this->successResponse(['message' => 'Persona validada exitosamente.', 'data' => $model->fresh()]);
    }

    #[OA\Delete(
        path: '/personas/{persona}',
        operationId: 'deletePersona',
        tags: ['Personas'],
        summary: 'Desactivar persona',
        description: 'Desactiva el perfil sin eliminarlo de la base de datos. Rate limit: 30 solicitudes por minuto por IP.',
        parameters: [
            new OA\Parameter(name: 'persona', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'), example: '550e8400-e29b-41d4-a716-446655440000'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Persona desactivada',
                content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'No encontrada',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function destroy(string $persona): JsonResponse
    {
        $model = Persona::find($persona);
        if (! $model) {
            return $this->errorResponse('Persona no encontrada.', 404);
        }
        $model->update(['activo' => false]);

        return $this->successResponse(['message' => 'Persona desactivada exitosamente.']);
    }

    private function generarCodigoTalento(): string
    {
        do {
            $codigo = 'PROV-'.date('Y').'-'.strtoupper(Str::random(4));
        } while (Persona::where('codigo_talento', $codigo)->exists());

        return $codigo;
    }

    private function calcularCompletitud(array $data): int
    {
        $campos = ['email', 'telefono', 'resumen', 'nivel_educacional', 'titulo_carrera',
            'anio_egreso', 'anios_experiencia', 'competencias', 'rango_renta',
            'tipo_jornada', 'modalidad'];
        $completados = count(array_filter($campos, fn ($c) => ! empty($data[$c])));

        return (int) round(($completados / count($campos)) * 100);
    }
}
