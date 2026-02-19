<?php

namespace Database\Factories;

use App\Models\EnvioInventario;
use App\Models\Responsable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SolicitudAjusteInventario>
 */
class SolicitudAjusteInventarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'envio_inventario_id' => EnvioInventario::factory(),
            'responsable_id' => Responsable::factory(),
            'ubicacion_id' => null,
            'tipo_solicitud' => 'ajuste_general',
            'estado' => 'pendiente',
            'solicitante_nombre' => fake()->name(),
            'medio_contacto' => fake()->randomElement(['correo', 'whatsapp']),
            'contacto_detalle' => fake()->randomElement([fake()->safeEmail(), fake()->numerify('3#########')]),
            'franja_horaria' => fake()->randomElement(['7:00 - 8:00', '9:00 - 10:00', '11:00 - 12:00']),
            'detalle' => fake()->sentence(12),
            'confirmado_coordinacion' => true,
            'solicitado_at' => now(),
            'revisado_por_user_id' => null,
            'revisado_at' => null,
            'observacion_admin' => null,
        ];
    }
}
