<?php

namespace Tests\Feature;

use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_active_empresas(): void
    {
        $visible = Empresa::factory()->create([
            'email' => 'visible@empresa.cl',
            'activo' => true,
        ]);
        Empresa::factory()->create([
            'email' => 'inactive@empresa.cl',
            'activo' => false,
        ]);

        $response = $this->getJson('/api/empresas');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $visible->id)
            ->assertJsonPath('data.0.email', $visible->email);
    }

    public function test_can_filter_empresas_by_tipo_empresa(): void
    {
        $empresa = Empresa::factory()->create([
            'tipo_empresa' => 'est',
            'activo' => true,
        ]);
        Empresa::factory()->create([
            'tipo_empresa' => 'outsourcing',
            'activo' => true,
        ]);

        $response = $this->getJson('/api/empresas?tipo_empresa=est');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $empresa->id)
            ->assertJsonPath('data.0.tipo_empresa', 'est');
    }

    public function test_can_create_empresa(): void
    {
        $payload = [
            'nombre_empresa' => 'Tech Corp SpA',
            'rut_empresa' => '76123456-7',
            'email' => 'rrhh@techcorp.cl',
            'logo_url' => 'https://example.com/logo.png',
            'rubro' => 'Tecnologia',
            'tipo_empresa' => 'contratacion-directa',
            'presentacion' => 'Empresa enfocada en desarrollo de software.',
            'beneficios' => ['Trabajo remoto', 'Seguro complementario'],
            'contacto_nombre' => 'Ana Lopez',
            'contacto_email' => 'ana@techcorp.cl',
            'contacto_telefono' => '+56912345678',
        ];

        $response = $this->postJson('/api/empresas', $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', $payload['email'])
            ->assertJsonPath('data.tipo_empresa', $payload['tipo_empresa']);

        $this->assertDatabaseHas('empresas', [
            'email' => $payload['email'],
            'rut_empresa' => $payload['rut_empresa'],
        ]);
    }

    public function test_create_empresa_returns_validation_errors(): void
    {
        $response = $this->postJson('/api/empresas', [
            'nombre_empresa' => '',
            'rut_empresa' => '',
            'email' => 'correo-invalido',
            'tipo_empresa' => 'otra',
            'contacto_email' => 'no-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Los datos enviados no son válidos.')
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['nombre_empresa', 'rut_empresa', 'email', 'tipo_empresa', 'contacto_nombre', 'contacto_email'],
            ]);
    }

    public function test_can_show_empresa_by_uuid(): void
    {
        $empresa = Empresa::factory()->create();

        $response = $this->getJson("/api/empresas/{$empresa->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $empresa->id)
            ->assertJsonPath('data.email', $empresa->email);
    }

    public function test_show_empresa_returns_404_for_unknown_uuid(): void
    {
        $response = $this->getJson('/api/empresas/550e8400-e29b-41d4-a716-446655440199');

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Empresa no encontrada.',
            ]);
    }

    public function test_can_update_empresa(): void
    {
        $empresa = Empresa::factory()->create([
            'email' => 'anterior@empresa.cl',
            'tipo_empresa' => 'est',
            'contacto_nombre' => 'Contacto Anterior',
        ]);

        $response = $this->putJson("/api/empresas/{$empresa->id}", [
            'email' => 'actualizada@empresa.cl',
            'tipo_empresa' => 'outsourcing',
            'contacto_nombre' => 'Nuevo Contacto',
            'beneficios' => ['Capacitaciones'],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'actualizada@empresa.cl')
            ->assertJsonPath('data.tipo_empresa', 'outsourcing');

        $this->assertDatabaseHas('empresas', [
            'id' => $empresa->id,
            'email' => 'actualizada@empresa.cl',
            'tipo_empresa' => 'outsourcing',
        ]);
    }

    public function test_update_empresa_returns_404_for_unknown_uuid(): void
    {
        $response = $this->putJson('/api/empresas/550e8400-e29b-41d4-a716-446655440199', [
            'email' => 'actualizada@empresa.cl',
        ]);

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Empresa no encontrada.',
            ]);
    }

    public function test_update_empresa_returns_validation_errors(): void
    {
        $empresa = Empresa::factory()->create();

        $response = $this->putJson("/api/empresas/{$empresa->id}", [
            'email' => 'correo-invalido',
            'tipo_empresa' => 'otra',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['email', 'tipo_empresa'],
            ]);
    }

    public function test_can_validar_empresa(): void
    {
        $empresa = Empresa::factory()->create(['validado' => false]);

        $response = $this->patchJson("/api/empresas/{$empresa->id}/validar");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.message', 'Empresa validada exitosamente.')
            ->assertJsonPath('data.data.id', $empresa->id)
            ->assertJsonPath('data.data.validado', true);

        $this->assertDatabaseHas('empresas', [
            'id' => $empresa->id,
            'validado' => true,
        ]);
    }

    public function test_validar_empresa_returns_404_for_unknown_uuid(): void
    {
        $response = $this->patchJson('/api/empresas/550e8400-e29b-41d4-a716-446655440199/validar');

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Empresa no encontrada.',
            ]);
    }

    public function test_can_deactivate_empresa(): void
    {
        $empresa = Empresa::factory()->create(['activo' => true]);

        $response = $this->deleteJson("/api/empresas/{$empresa->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.message', 'Empresa desactivada exitosamente.');

        $this->assertDatabaseHas('empresas', [
            'id' => $empresa->id,
            'activo' => false,
        ]);
    }

    public function test_deactivate_empresa_returns_404_for_unknown_uuid(): void
    {
        $response = $this->deleteJson('/api/empresas/550e8400-e29b-41d4-a716-446655440199');

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Empresa no encontrada.',
            ]);
    }
}
