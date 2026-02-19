<?php

namespace Database\Factories;

use App\Models\Responsable;
use App\Models\Sede;
use App\Models\Ubicacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ubicacion>
 */
class UbicacionFactory extends Factory
{
    protected $model = Ubicacion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sede_id' => Sede::factory(),
            'nombre' => 'Aula '.fake()->unique()->numerify('###'),
            'codigo' => fake()->unique()->bothify('A###'),
            'tipo' => fake()->randomElement(['aula', 'laboratorio', 'oficina']),
            'responsable_id' => Responsable::factory(),
            'piso' => fake()->numberBetween(1, 4),
            'capacidad' => fake()->numberBetween(10, 45),
            'observaciones' => fake()->optional()->sentence(),
            'activo' => true,
        ];
    }
}
