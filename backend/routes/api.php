<?php

use App\Http\Controllers\AdministracionController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PersonaController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api-read')->get('/health', HealthController::class);

Route::middleware('throttle:api-read')->group(function () {
    Route::get('personas', [PersonaController::class, 'index']);
    Route::get('personas/{persona}', [PersonaController::class, 'show']);
    Route::get('empresas', [EmpresaController::class, 'index']);
    Route::get('empresas/{empresa}', [EmpresaController::class, 'show']);
});

Route::middleware('throttle:api-write')->group(function () {
    Route::post('personas', [PersonaController::class, 'store']);
    Route::put('personas/{persona}', [PersonaController::class, 'update']);
    Route::patch('personas/{persona}/validar', [PersonaController::class, 'validar']);
    Route::delete('personas/{persona}', [PersonaController::class, 'destroy']);
    Route::post('empresas', [EmpresaController::class, 'store']);
    Route::put('empresas/{empresa}', [EmpresaController::class, 'update']);
    Route::patch('empresas/{empresa}/validar', [EmpresaController::class, 'validar']);
    Route::delete('empresas/{empresa}', [EmpresaController::class, 'destroy']);
});

Route::prefix('admin')->group(function () {
    Route::middleware('throttle:api-admin-read')->group(function () {
        Route::get('contactos', [AdministracionController::class, 'listarContactos']);
        Route::get('estadisticas', [AdministracionController::class, 'estadisticas']);
    });

    Route::middleware('throttle:api-admin-write')->group(function () {
        Route::post('contactos', [AdministracionController::class, 'crearContacto']);
        Route::patch('contactos/{contacto}/estado', [AdministracionController::class, 'actualizarEstado']);
    });
});
