<?php

namespace Database\Factories;

use App\Models\ContactoSolicitado;
use App\Models\Empresa;
use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactoSolicitado>
 */
class ContactoSolicitadoFactory extends Factory
{
    protected $model = ContactoSolicitado::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'persona_id' => Persona::factory(),
            'estado' => 'pendiente',
            'notas_admin' => fake()->optional()->sentence(),
            'fecha_contacto' => null,
            'fecha_entrevista' => null,
            'fecha_resultado' => null,
        ];
    }
}
