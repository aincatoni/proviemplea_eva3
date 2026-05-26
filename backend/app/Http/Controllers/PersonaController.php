<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonaController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->successResponse(Persona::all());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:personas,email',
            'codigo_talento' => 'required|string|unique:personas,codigo_talento',
        ]);

        $persona = Persona::create($request->all());

        return $this->successResponse($persona, 201);
    }

    public function show(Persona $persona): JsonResponse
    {
        return $this->successResponse($persona);
    }

    public function update(Request $request, Persona $persona): JsonResponse
    {
        $request->validate([
            'email' => 'sometimes|email|unique:personas,email,'.$persona->id,
            'codigo_talento' => 'sometimes|string|unique:personas,codigo_talento,'.$persona->id,
        ]);

        $persona->update($request->all());

        return $this->successResponse($persona);
    }

    public function destroy(Persona $persona): JsonResponse
    {
        $persona->delete();

        return $this->successResponse(['message' => 'Persona eliminada correctamente']);
    }

    public function validar(Persona $persona): JsonResponse
    {
        $persona->update(['validado' => true]);

        return $this->successResponse($persona);
    }
}
