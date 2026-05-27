<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class EmpresaController extends Controller
{
    #[OA\Get(
        path: '/empresas',
        operationId: 'getEmpresas',
        tags: ['Empresas'],
        summary: 'Listar empresas validadas',
        description: 'Obtiene empresas activas. Rate limit: 120 solicitudes por minuto por IP. Se recomienda filtrar por tipo cuando se integre desde clientes externos.',
        parameters: [
            new OA\Parameter(name: 'tipo_empresa', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['contratacion-directa', 'est', 'outsourcing'])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado exitoso',
                content: new OA\JsonContent(ref: '#/components/schemas/EmpresaListResponse')
            ),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Empresa::where('activo', true);
        if ($request->has('tipo_empresa')) {
            $query->where('tipo_empresa', $request->input('tipo_empresa'));
        }

        return $this->successResponse($query->get());
    }

    #[OA\Post(
        path: '/empresas',
        operationId: 'createEmpresa',
        tags: ['Empresas'],
        summary: 'Registrar nueva empresa',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/EmpresaInput')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Empresa creada',
                content: new OA\JsonContent(ref: '#/components/schemas/EmpresaResponse')
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
            'nombre_empresa' => 'required|string|max:255',
            'rut_empresa' => 'required|string|max:20|unique:empresas,rut_empresa',
            'email' => 'required|email|unique:empresas,email',
            'logo_url' => 'nullable|url',
            'rubro' => 'nullable|string|max:100',
            'tipo_empresa' => 'required|in:contratacion-directa,est,outsourcing',
            'presentacion' => 'nullable|string',
            'beneficios' => 'nullable|array',
            'beneficios.*' => 'string',
            'contacto_nombre' => 'required|string|max:100',
            'contacto_email' => 'required|email',
            'contacto_telefono' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son válidos.', 422, $validator->errors()->toArray());
        }

        return $this->successResponse(Empresa::create($validator->validated()), 201);
    }

    #[OA\Get(
        path: '/empresas/{empresa}',
        operationId: 'getEmpresa',
        tags: ['Empresas'],
        summary: 'Obtener empresa por ID',
        description: 'Consulta de lectura con rate limit de 120 solicitudes por minuto por IP.',
        parameters: [
            new OA\Parameter(name: 'empresa', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'), example: '550e8400-e29b-41d4-a716-446655440001'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Empresa encontrada',
                content: new OA\JsonContent(ref: '#/components/schemas/EmpresaResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'No encontrada',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function show(string $empresa): JsonResponse
    {
        $model = Empresa::find($empresa);
        if (! $model) {
            return $this->errorResponse('Empresa no encontrada.', 404);
        }

        return $this->successResponse($model);
    }

    #[OA\Put(
        path: '/empresas/{empresa}',
        operationId: 'updateEmpresa',
        tags: ['Empresas'],
        summary: 'Actualizar empresa',
        parameters: [
            new OA\Parameter(name: 'empresa', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'), example: '550e8400-e29b-41d4-a716-446655440001'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/EmpresaInput')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Empresa actualizada',
                content: new OA\JsonContent(ref: '#/components/schemas/EmpresaResponse')
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
    public function update(Request $request, string $empresa): JsonResponse
    {
        $model = Empresa::find($empresa);
        if (! $model) {
            return $this->errorResponse('Empresa no encontrada.', 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre_empresa' => 'sometimes|string|max:255',
            'rut_empresa' => 'sometimes|string|max:20|unique:empresas,rut_empresa,'.$model->id,
            'email' => 'sometimes|email|unique:empresas,email,'.$model->id,
            'logo_url' => 'nullable|url',
            'rubro' => 'nullable|string|max:100',
            'tipo_empresa' => 'sometimes|in:contratacion-directa,est,outsourcing',
            'presentacion' => 'nullable|string',
            'beneficios' => 'nullable|array',
            'beneficios.*' => 'string',
            'contacto_nombre' => 'sometimes|string|max:100',
            'contacto_email' => 'sometimes|email',
            'contacto_telefono' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son válidos.', 422, $validator->errors()->toArray());
        }

        $model->update($validator->validated());

        return $this->successResponse($model->fresh());
    }

    #[OA\Patch(
        path: '/empresas/{empresa}/validar',
        operationId: 'validarEmpresa',
        tags: ['Empresas'],
        summary: 'Validar empresa (solo administración)',
        description: 'Marca una empresa como validada. Rate limit: 30 solicitudes por minuto por IP.',
        parameters: [
            new OA\Parameter(name: 'empresa', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'), example: '550e8400-e29b-41d4-a716-446655440001'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Empresa validada',
                content: new OA\JsonContent(ref: '#/components/schemas/EmpresaValidationResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'No encontrada',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function validar(string $empresa): JsonResponse
    {
        $model = Empresa::find($empresa);
        if (! $model) {
            return $this->errorResponse('Empresa no encontrada.', 404);
        }
        $model->update(['validado' => true]);

        return $this->successResponse(['message' => 'Empresa validada exitosamente.', 'data' => $model->fresh()]);
    }

    #[OA\Delete(
        path: '/empresas/{empresa}',
        operationId: 'deleteEmpresa',
        tags: ['Empresas'],
        summary: 'Desactivar empresa',
        description: 'Desactiva el perfil sin eliminarlo de la base de datos. Rate limit: 30 solicitudes por minuto por IP.',
        parameters: [
            new OA\Parameter(name: 'empresa', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'), example: '550e8400-e29b-41d4-a716-446655440001'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Empresa desactivada',
                content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'No encontrada',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function destroy(string $empresa): JsonResponse
    {
        $model = Empresa::find($empresa);
        if (! $model) {
            return $this->errorResponse('Empresa no encontrada.', 404);
        }
        $model->update(['activo' => false]);

        return $this->successResponse(['message' => 'Empresa desactivada exitosamente.']);
    }
}
