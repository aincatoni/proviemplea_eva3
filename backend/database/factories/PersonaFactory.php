<?php

namespace Database\Factories;

use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Persona>
 */
class PersonaFactory extends Factory
{
    protected $model = Persona::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = (int) fake()->numberBetween(2015, (int) date('Y'));

        return [
            'email' => fake()->unique()->safeEmail(),
            'telefono' => fake()->optional()->numerify('+569########'),
            'comprobante_residencia' => fake()->optional()->url(),
            'codigo_talento' => 'PROV-'.date('Y').'-'.strtoupper(fake()->unique()->bothify('??##')),
            'resumen' => fake()->optional()->paragraph(),
            'nivel_educacional' => fake()->randomElement(['basica', 'media', 'tecnica', 'universitaria', 'postgrado']),
            'titulo_carrera' => fake()->optional()->randomElement(['Ingeniería Informática', 'Administración', 'Diseño Gráfico', 'Contabilidad']),
            'anio_egreso' => $year,
            'anios_experiencia' => fake()->numberBetween(0, 10),
            'areas_experiencia' => fake()->randomElements(['Desarrollo Web', 'APIs REST', 'Soporte TI', 'Ventas', 'Atención al Cliente'], fake()->numberBetween(1, 3)),
            'competencias' => fake()->randomElements(['PHP', 'Laravel', 'MySQL', 'Docker', 'Excel', 'JavaScript'], fake()->numberBetween(2, 4)),
            'rango_renta' => fake()->randomElement(['500k-800k', '800k-1.2M', '1.2M-1.8M']),
            'tipo_jornada' => fake()->randomElement(['completa', 'part-time', 'por-horas']),
            'modalidad' => fake()->randomElement(['presencial', 'remoto', 'hibrido']),
            'cursos' => [
                [
                    'nombre' => fake()->randomElement(['Laravel', 'Excel Intermedio', 'Atención al Cliente']),
                    'institucion' => fake()->company(),
                    'anio' => fake()->numberBetween(max(2010, $year - 2), (int) date('Y')),
                ],
            ],
            'idiomas' => [
                [
                    'idioma' => fake()->randomElement(['Inglés', 'Portugués']),
                    'nivel' => fake()->randomElement(['basico', 'intermedio', 'avanzado', 'nativo']),
                ],
            ],
            'portafolio_url' => fake()->optional()->url(),
            'persona_discapacidad' => fake()->boolean(20),
            'validado' => fake()->boolean(60),
            'activo' => true,
            'porcentaje_completitud' => fake()->numberBetween(60, 100),
        ];
    }
}
