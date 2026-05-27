<?php

namespace Tests\Feature;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_active_personas_as_blind_cv(): void
    {
        $visible = Persona::factory()->create([
            'email' => 'visible@example.com',
            'activo' => true,
            'validado' => true,
        ]);
        Persona::factory()->create([
            'email' => 'inactive@example.com',
            'activo' => false,
        ]);

        $response = $this->getJson('/api/personas');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $visible->id)
            ->assertJsonMissingPath('data.0.email');
    }

    public function test_can_filter_personas_by_validado(): void
    {
        $validated = Persona::factory()->create(['validado' => true, 'activo' => true]);
        Persona::factory()->create(['validado' => false, 'activo' => true]);

        $response = $this->getJson('/api/personas?validado=1');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $validated->id);
    }

    public function test_can_create_persona(): void
    {
        $payload = [
            'email' => 'nueva.persona@example.com',
            'telefono' => '+56912345678',
            'resumen' => 'Perfil orientado a desarrollo backend.',
            'nivel_educacional' => 'universitaria',
            'titulo_carrera' => 'Ingenieria en Informatica',
            'anio_egreso' => 2022,
            'anios_experiencia' => 2,
            'areas_experiencia' => ['Desarrollo Web', 'APIs REST'],
            'competencias' => ['PHP', 'Laravel'],
            'rango_renta' => '800k-1.2M',
            'tipo_jornada' => 'completa',
            'modalidad' => 'hibrido',
            'portafolio_url' => 'https://example.com/portafolio',
            'persona_discapacidad' => false,
        ];

        $response = $this->postJson('/api/personas', $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', $payload['email'])
            ->assertJsonPath('data.codigo_talento', fn (string $codigo) => str_starts_with($codigo, 'PROV-'));

        $this->assertDatabaseHas('personas', [
            'email' => $payload['email'],
        ]);
    }

    public function test_create_persona_returns_validation_errors(): void
    {
        $response = $this->postJson('/api/personas', [
            'email' => 'correo-invalido',
            'nivel_educacional' => 'doctorado',
            'portafolio_url' => 'no-es-url',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Los datos enviados no son válidos.')
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['email', 'nivel_educacional', 'portafolio_url'],
            ]);
    }

    public function test_can_show_persona_by_uuid(): void
    {
        $persona = Persona::factory()->create();

        $response = $this->getJson("/api/personas/{$persona->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $persona->id)
            ->assertJsonPath('data.email', $persona->email);
    }

    public function test_show_persona_returns_404_for_unknown_uuid(): void
    {
        $response = $this->getJson('/api/personas/550e8400-e29b-41d4-a716-446655440099');

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Persona no encontrada.',
            ]);
    }

    public function test_can_update_persona(): void
    {
        $persona = Persona::factory()->create([
            'email' => 'anterior@example.com',
            'resumen' => 'Anterior',
            'modalidad' => 'presencial',
        ]);

        $response = $this->putJson("/api/personas/{$persona->id}", [
            'email' => 'actualizada@example.com',
            'resumen' => 'Perfil actualizado',
            'modalidad' => 'remoto',
            'anios_experiencia' => 4,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'actualizada@example.com')
            ->assertJsonPath('data.modalidad', 'remoto');

        $this->assertDatabaseHas('personas', [
            'id' => $persona->id,
            'email' => 'actualizada@example.com',
            'modalidad' => 'remoto',
        ]);
    }

    public function test_update_persona_returns_404_for_unknown_uuid(): void
    {
        $response = $this->putJson('/api/personas/550e8400-e29b-41d4-a716-446655440099', [
            'email' => 'actualizada@example.com',
        ]);

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Persona no encontrada.',
            ]);
    }

    public function test_update_persona_returns_validation_errors(): void
    {
        $persona = Persona::factory()->create();

        $response = $this->putJson("/api/personas/{$persona->id}", [
            'email' => 'correo-invalido',
            'anio_egreso' => 1900,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['email', 'anio_egreso'],
            ]);
    }

    public function test_can_validar_persona(): void
    {
        $persona = Persona::factory()->create(['validado' => false]);

        $response = $this->patchJson("/api/personas/{$persona->id}/validar");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.message', 'Persona validada exitosamente.')
            ->assertJsonPath('data.data.id', $persona->id)
            ->assertJsonPath('data.data.validado', true);

        $this->assertDatabaseHas('personas', [
            'id' => $persona->id,
            'validado' => true,
        ]);
    }

    public function test_validar_persona_returns_404_for_unknown_uuid(): void
    {
        $response = $this->patchJson('/api/personas/550e8400-e29b-41d4-a716-446655440099/validar');

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Persona no encontrada.',
            ]);
    }

    public function test_can_deactivate_persona(): void
    {
        $persona = Persona::factory()->create(['activo' => true]);

        $response = $this->deleteJson("/api/personas/{$persona->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.message', 'Persona desactivada exitosamente.');

        $this->assertDatabaseHas('personas', [
            'id' => $persona->id,
            'activo' => false,
        ]);
    }

    public function test_deactivate_persona_returns_404_for_unknown_uuid(): void
    {
        $response = $this->deleteJson('/api/personas/550e8400-e29b-41d4-a716-446655440099');

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Persona no encontrada.',
            ]);
    }
}
