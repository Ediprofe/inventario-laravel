<?php

namespace Database\Factories;

use App\Models\EnvioInventario;
use App\Models\Responsable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EnvioInventario>
 */
class EnvioInventarioFactory extends Factory
{
    protected $model = EnvioInventario::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'responsable_id' => Responsable::factory(),
            'tipo' => 'por_responsable',
            'ubicacion_id' => null,
            'email_enviado_a' => fake()->safeEmail(),
            'enviado_at' => now(),
            'token' => (string) Str::uuid(),
            'aprobado_at' => null,
            'ip_aprobacion' => null,
            'firmante_nombre' => null,
            'firma_base64' => null,
            'observaciones' => null,
        ];
    }
}
