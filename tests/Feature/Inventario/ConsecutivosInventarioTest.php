<?php

namespace Tests\Feature\Inventario;

use App\Models\EnvioInventario;
use App\Models\Responsable;
use App\Models\SolicitudAjusteInventario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsecutivosInventarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_reinicia_consecutivo_de_envios_despues_de_eliminar_registros(): void
    {
        EnvioInventario::factory()->count(2)->create();

        EnvioInventario::query()->delete();
        EnvioInventario::resetConsecutive();

        $nuevoEnvio = EnvioInventario::factory()->create();

        $this->assertSame(1, $nuevoEnvio->id);
    }

    public function test_reinicia_consecutivo_de_solicitudes_despues_de_eliminar_registros(): void
    {
        $responsable = Responsable::factory()->create();
        $envio = EnvioInventario::factory()->create([
            'responsable_id' => $responsable->id,
            'email_enviado_a' => $responsable->email,
        ]);

        SolicitudAjusteInventario::factory()->count(2)->create([
            'envio_inventario_id' => $envio->id,
            'responsable_id' => $responsable->id,
        ]);

        SolicitudAjusteInventario::query()->delete();
        SolicitudAjusteInventario::resetConsecutive();

        $nuevaSolicitud = SolicitudAjusteInventario::factory()->create([
            'envio_inventario_id' => $envio->id,
            'responsable_id' => $responsable->id,
        ]);

        $this->assertSame(1, $nuevaSolicitud->id);
    }
}
