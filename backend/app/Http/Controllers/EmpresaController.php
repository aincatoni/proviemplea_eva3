<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->successResponse(Empresa::all());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre_empresa' => 'required|string',
            'rut_empresa' => 'required|string|unique:empresas,rut_empresa',
            'email' => 'required|email|unique:empresas,email',
            'tipo_empresa' => 'required|string',
            'contacto_nombre' => 'required|string',
            'contacto_email' => 'required|email',
        ]);

        $empresa = Empresa::create($request->all());

        return $this->successResponse($empresa, 201);
    }

    public function show(Empresa $empresa): JsonResponse
    {
        return $this->successResponse($empresa);
    }

    public function update(Request $request, Empresa $empresa): JsonResponse
    {
        $request->validate([
            'rut_empresa' => 'sometimes|string|unique:empresas,rut_empresa,'.$empresa->id,
            'email' => 'sometimes|email|unique:empresas,email,'.$empresa->id,
        ]);

        $empresa->update($request->all());

        return $this->successResponse($empresa);
    }

    public function destroy(Empresa $empresa): JsonResponse
    {
        $empresa->delete();

        return $this->successResponse(['message' => 'Empresa eliminada correctamente']);
    }

    public function validar(Empresa $empresa): JsonResponse
    {
        $empresa->update(['validado' => true]);

        return $this->successResponse($empresa);
    }
}
