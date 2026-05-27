<?php

namespace Database\Factories;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Empresa>
 */
class EmpresaFactory extends Factory
{
    protected $model = Empresa::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre_empresa' => fake()->unique()->company(),
            'rut_empresa' => fake()->unique()->numerify('########-#'),
            'email' => fake()->unique()->companyEmail(),
            'logo_url' => fake()->optional()->url(),
            'rubro' => fake()->randomElement(['Tecnología', 'Retail', 'Salud', 'Educación', 'Logística']),
            'tipo_empresa' => fake()->randomElement(['contratacion-directa', 'est', 'outsourcing']),
            'presentacion' => fake()->optional()->paragraph(),
            'beneficios' => fake()->randomElements(['Seguro complementario', 'Trabajo remoto', 'Capacitaciones', 'Bono anual'], fake()->numberBetween(1, 3)),
            'contacto_nombre' => fake()->name(),
            'contacto_email' => fake()->unique()->safeEmail(),
            'contacto_telefono' => fake()->optional()->numerify('+569########'),
            'validado' => fake()->boolean(60),
            'activo' => true,
        ];
    }
}
