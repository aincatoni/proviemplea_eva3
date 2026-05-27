<?php

namespace App\Http\Controllers\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorResponse',
    title: 'Error Response',
    description: 'Respuesta estándar de error de la API',
    required: ['success', 'message'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Recurso no encontrado.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(type: 'array', items: new OA\Items(type: 'string')),
            nullable: true,
            example: ['email' => ['El campo email es obligatorio.']]
        ),
    ]
)]
#[OA\Schema(
    schema: 'ActionMessage',
    title: 'Action Message',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Persona desactivada exitosamente.'),
    ]
)]
#[OA\Schema(
    schema: 'PersonaValidationResult',
    title: 'Persona Validation Result',
    required: ['message', 'data'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Persona validada exitosamente.'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Persona'),
    ]
)]
#[OA\Schema(
    schema: 'EmpresaValidationResult',
    title: 'Empresa Validation Result',
    required: ['message', 'data'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Empresa validada exitosamente.'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Empresa'),
    ]
)]
#[OA\Schema(
    schema: 'Estadisticas',
    title: 'Estadísticas',
    required: ['total_personas', 'personas_validadas', 'total_empresas', 'empresas_validadas', 'contactos_pendientes', 'contactos_en_proceso', 'contactos_exitosos'],
    properties: [
        new OA\Property(property: 'total_personas', type: 'integer', example: 45),
        new OA\Property(property: 'personas_validadas', type: 'integer', example: 38),
        new OA\Property(property: 'total_empresas', type: 'integer', example: 12),
        new OA\Property(property: 'empresas_validadas', type: 'integer', example: 10),
        new OA\Property(property: 'contactos_pendientes', type: 'integer', example: 5),
        new OA\Property(property: 'contactos_en_proceso', type: 'integer', example: 8),
        new OA\Property(property: 'contactos_exitosos', type: 'integer', example: 15),
    ]
)]
#[OA\Schema(
    schema: 'PersonaListResponse',
    title: 'Persona List Response',
    required: ['success', 'data'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/PersonaCVCiego')),
    ]
)]
#[OA\Schema(
    schema: 'PersonaResponse',
    title: 'Persona Response',
    required: ['success', 'data'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'data', ref: '#/components/schemas/Persona'),
    ]
)]
#[OA\Schema(
    schema: 'PersonaValidationResponse',
    title: 'Persona Validation Response',
    required: ['success', 'data'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'data', ref: '#/components/schemas/PersonaValidationResult'),
    ]
)]
#[OA\Schema(
    schema: 'MessageResponse',
    title: 'Message Response',
    required: ['success', 'data'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'data', ref: '#/components/schemas/ActionMessage'),
    ]
)]
#[OA\Schema(
    schema: 'EmpresaListResponse',
    title: 'Empresa List Response',
    required: ['success', 'data'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Empresa')),
    ]
)]
#[OA\Schema(
    schema: 'EmpresaResponse',
    title: 'Empresa Response',
    required: ['success', 'data'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'data', ref: '#/components/schemas/Empresa'),
    ]
)]
#[OA\Schema(
    schema: 'EmpresaValidationResponse',
    title: 'Empresa Validation Response',
    required: ['success', 'data'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'data', ref: '#/components/schemas/EmpresaValidationResult'),
    ]
)]
#[OA\Schema(
    schema: 'ContactoSolicitadoListResponse',
    title: 'Contacto Solicitado List Response',
    required: ['success', 'data'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ContactoSolicitado')),
    ]
)]
#[OA\Schema(
    schema: 'ContactoSolicitadoResponse',
    title: 'Contacto Solicitado Response',
    required: ['success', 'data'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'data', ref: '#/components/schemas/ContactoSolicitado'),
    ]
)]
#[OA\Schema(
    schema: 'EstadisticasResponse',
    title: 'Estadísticas Response',
    required: ['success', 'data'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'data', ref: '#/components/schemas/Estadisticas'),
    ]
)]
class ResponseSchema {}
