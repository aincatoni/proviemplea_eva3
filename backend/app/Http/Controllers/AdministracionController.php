<?php

namespace App\Http\Controllers;

use App\Models\ContactoSolicitado;
use App\Models\Empresa;
use App\Models\Persona;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdministracionController extends Controller
{
    public function listarContactos(): JsonResponse
    {
        $contactos = ContactoSolicitado::with(['empresa', 'persona'])->get();

        return $this->successResponse($contactos);
    }

    public function crearContacto(Request $request): JsonResponse
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'persona_id' => 'required|exists:personas,id',
        ]);

        $contacto = ContactoSolicitado::create($request->all());

        return $this->successResponse($contacto->load(['empresa', 'persona']), 201);
    }

    public function actualizarEstado(Request $request, ContactoSolicitado $contacto): JsonResponse
    {
        $request->validate([
            'estado' => 'required|in:pendiente,contactado,entrevista,seleccionado,no-seleccionado,proceso-cerrado',
        ]);

        $contacto->update($request->only('estado', 'notas_admin', 'fecha_contacto', 'fecha_entrevista', 'fecha_resultado'));

        return $this->successResponse($contacto);
    }

    public function estadisticas(): JsonResponse
    {
        return $this->successResponse([
            'total_personas' => Persona::count(),
            'personas_activas' => Persona::where('activo', true)->count(),
            'total_empresas' => Empresa::count(),
            'total_contactos' => ContactoSolicitado::count(),
            'por_estado' => ContactoSolicitado::selectRaw('estado, count(*) as total')
                ->groupBy('estado')
                ->pluck('total', 'estado'),
        ]);
    }
}
