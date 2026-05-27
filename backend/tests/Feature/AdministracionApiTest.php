<?php

namespace Tests\Feature;

use App\Models\ContactoSolicitado;
use App\Models\Empresa;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministracionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_contacto_solicitado(): void
    {
        $empresa = Empresa::factory()->create();
        $persona = Persona::factory()->create();

        $response = $this->postJson('/api/admin/contactos', [
            'empresa_id' => $empresa->id,
            'persona_id' => $persona->id,
            'notas_admin' => 'Contacto prioritario.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.empresa_id', $empresa->id)
            ->assertJsonPath('data.persona_id', $persona->id)
            ->assertJsonPath('data.estado', 'pendiente')
            ->assertJsonPath('data.empresa.id', $empresa->id)
            ->assertJsonPath('data.persona.id', $persona->id);

        $this->assertDatabaseHas('contactos_solicitados', [
            'empresa_id' => $empresa->id,
            'persona_id' => $persona->id,
            'estado' => 'pendiente',
        ]);
    }

    public function test_create_contacto_returns_validation_errors(): void
    {
        $response = $this->postJson('/api/admin/contactos', [
            'empresa_id' => 'no-existe',
            'persona_id' => 'no-existe',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Los datos enviados no son válidos.')
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['empresa_id', 'persona_id'],
            ]);
    }

    public function test_cannot_create_duplicate_active_contacto(): void
    {
        $empresa = Empresa::factory()->create();
        $persona = Persona::factory()->create();

        ContactoSolicitado::factory()->create([
            'empresa_id' => $empresa->id,
            'persona_id' => $persona->id,
            'estado' => 'contactado',
        ]);

        $response = $this->postJson('/api/admin/contactos', [
            'empresa_id' => $empresa->id,
            'persona_id' => $persona->id,
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Ya existe una solicitud activa entre esta empresa y talento.',
            ]);
    }

    public function test_can_create_contacto_after_closed_process(): void
    {
        $empresa = Empresa::factory()->create();
        $persona = Persona::factory()->create();

        ContactoSolicitado::factory()->create([
            'empresa_id' => $empresa->id,
            'persona_id' => $persona->id,
            'estado' => 'proceso-cerrado',
        ]);

        $response = $this->postJson('/api/admin/contactos', [
            'empresa_id' => $empresa->id,
            'persona_id' => $persona->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('contactos_solicitados', 2);
    }

    public function test_can_list_contactos_and_filter_by_estado(): void
    {
        $contacto = ContactoSolicitado::factory()->create(['estado' => 'entrevista']);
        ContactoSolicitado::factory()->create(['estado' => 'pendiente']);

        $response = $this->getJson('/api/admin/contactos?estado=entrevista');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $contacto->id)
            ->assertJsonPath('data.0.estado', 'entrevista');
    }

    public function test_can_update_contacto_estado_to_contactado_and_set_fecha_contacto(): void
    {
        $contacto = ContactoSolicitado::factory()->create([
            'estado' => 'pendiente',
            'fecha_contacto' => null,
        ]);

        $response = $this->patchJson("/api/admin/contactos/{$contacto->id}/estado", [
            'estado' => 'contactado',
            'notas_admin' => 'Primer contacto realizado.',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $contacto->id)
            ->assertJsonPath('data.estado', 'contactado')
            ->assertJsonPath('data.notas_admin', 'Primer contacto realizado.');

        $this->assertDatabaseHas('contactos_solicitados', [
            'id' => $contacto->id,
            'estado' => 'contactado',
        ]);

        $this->assertNotNull($contacto->fresh()->fecha_contacto);
    }

    public function test_can_update_contacto_estado_to_entrevista_and_set_fecha_entrevista(): void
    {
        $contacto = ContactoSolicitado::factory()->create([
            'estado' => 'contactado',
            'fecha_entrevista' => null,
        ]);

        $response = $this->patchJson("/api/admin/contactos/{$contacto->id}/estado", [
            'estado' => 'entrevista',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.estado', 'entrevista');

        $this->assertNotNull($contacto->fresh()->fecha_entrevista);
    }

    public function test_can_update_contacto_estado_to_seleccionado_and_set_fecha_resultado(): void
    {
        $contacto = ContactoSolicitado::factory()->create([
            'estado' => 'entrevista',
            'fecha_resultado' => null,
        ]);

        $response = $this->patchJson("/api/admin/contactos/{$contacto->id}/estado", [
            'estado' => 'seleccionado',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.estado', 'seleccionado');

        $this->assertNotNull($contacto->fresh()->fecha_resultado);
    }

    public function test_update_contacto_returns_404_for_unknown_uuid(): void
    {
        $response = $this->patchJson('/api/admin/contactos/550e8400-e29b-41d4-a716-446655440299/estado', [
            'estado' => 'contactado',
        ]);

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Contacto no encontrado.',
            ]);
    }

    public function test_update_contacto_returns_validation_errors(): void
    {
        $contacto = ContactoSolicitado::factory()->create();

        $response = $this->patchJson("/api/admin/contactos/{$contacto->id}/estado", [
            'estado' => 'otro',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['estado'],
            ]);
    }

    public function test_can_get_estadisticas(): void
    {
        $empresaBase = Empresa::factory()->create(['validado' => false]);
        $personaBase = Persona::factory()->create(['validado' => false]);

        Persona::factory()->count(2)->create(['validado' => true]);
        Persona::factory()->count(3)->create(['validado' => false]);
        Empresa::factory()->count(1)->create(['validado' => true]);
        Empresa::factory()->count(2)->create(['validado' => false]);
        ContactoSolicitado::factory()->count(2)->create(['empresa_id' => $empresaBase->id, 'persona_id' => $personaBase->id, 'estado' => 'pendiente']);
        ContactoSolicitado::factory()->count(3)->create(['empresa_id' => $empresaBase->id, 'persona_id' => $personaBase->id, 'estado' => 'contactado']);
        ContactoSolicitado::factory()->count(1)->create(['empresa_id' => $empresaBase->id, 'persona_id' => $personaBase->id, 'estado' => 'entrevista']);
        ContactoSolicitado::factory()->count(4)->create(['empresa_id' => $empresaBase->id, 'persona_id' => $personaBase->id, 'estado' => 'seleccionado']);

        $response = $this->getJson('/api/admin/estadisticas');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_personas', 6)
            ->assertJsonPath('data.personas_validadas', 2)
            ->assertJsonPath('data.total_empresas', 4)
            ->assertJsonPath('data.empresas_validadas', 1)
            ->assertJsonPath('data.contactos_pendientes', 2)
            ->assertJsonPath('data.contactos_en_proceso', 4)
            ->assertJsonPath('data.contactos_exitosos', 4);
    }
}
