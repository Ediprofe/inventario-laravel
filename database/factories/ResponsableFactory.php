<?php

namespace Database\Factories;

use App\Models\Responsable;
use App\Models\Sede;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Responsable>
 */
class ResponsableFactory extends Factory
{
    protected $model = Responsable::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'tipo_documento' => 'CC',
            'documento' => fake()->unique()->numerify('##########'),
            'cargo' => fake()->randomElement(['Docente', 'Coordinador', 'Auxiliar']),
            'email' => fake()->unique()->safeEmail(),
            'telefono' => fake()->numerify('3#########'),
            'sede_id' => Sede::factory(),
            'activo' => true,
            'es_firmante_entrega' => false,
            'firma_entrega_path' => null,
        ];
    }
}
