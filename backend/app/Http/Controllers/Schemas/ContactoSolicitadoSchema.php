<?php

namespace App\Http\Controllers\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ContactoSolicitado',
    title: 'Contacto Solicitado',
    description: 'Solicitud de contacto entre empresa y talento'
)]
class ContactoSolicitadoSchema
{
    #[OA\Property(property: 'id', type: 'integer', example: 1)]
    public int $id;

    #[OA\Property(property: 'empresa_id', type: 'integer', example: 1)]
    public int $empresa_id;

    #[OA\Property(property: 'persona_id', type: 'integer', example: 1)]
    public int $persona_id;

    #[OA\Property(property: 'estado', type: 'string', enum: ['pendiente', 'contactado', 'entrevista', 'seleccionado', 'no-seleccionado', 'proceso-cerrado'], example: 'pendiente')]
    public string $estado;

    #[OA\Property(property: 'notas_admin', type: 'string', nullable: true)]
    public ?string $notas_admin;

    #[OA\Property(property: 'fecha_contacto', type: 'string', format: 'date-time', nullable: true)]
    public ?string $fecha_contacto;

    #[OA\Property(property: 'fecha_entrevista', type: 'string', format: 'date-time', nullable: true)]
    public ?string $fecha_entrevista;

    #[OA\Property(property: 'fecha_resultado', type: 'string', format: 'date-time', nullable: true)]
    public ?string $fecha_resultado;

    #[OA\Property(property: 'created_at', type: 'string', format: 'date-time')]
    public string $created_at;

    #[OA\Property(property: 'updated_at', type: 'string', format: 'date-time')]
    public string $updated_at;

    #[OA\Property(property: 'empresa', ref: '#/components/schemas/Empresa')]
    public mixed $empresa;

    #[OA\Property(property: 'persona', ref: '#/components/schemas/Persona')]
    public mixed $persona;
}

#[OA\Schema(
    schema: 'ContactoSolicitadoInput',
    title: 'Contacto Solicitado Input',
    required: ['empresa_id', 'persona_id']
)]
class ContactoSolicitadoInputSchema
{
    #[OA\Property(property: 'empresa_id', type: 'integer', example: 1)]
    public int $empresa_id;

    #[OA\Property(property: 'persona_id', type: 'integer', example: 1)]
    public int $persona_id;

    #[OA\Property(property: 'notas_admin', type: 'string', nullable: true)]
    public ?string $notas_admin;
}
