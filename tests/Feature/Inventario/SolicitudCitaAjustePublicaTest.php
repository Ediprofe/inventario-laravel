<?php

namespace Tests\Feature\Inventario;

use App\Mail\InventarioReportMail;
use App\Models\EnvioInventario;
use App\Models\Responsable;
use App\Models\Ubicacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SolicitudCitaAjustePublicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_formulario_publico_de_cita_ajuste_carga_con_token_de_envio(): void
    {
        $envio = $this->crearEnvioPorUbicacion();

        $response = $this->get(route('inventario.cita-ajuste.mostrar', ['token' => $envio->token]));

        $response->assertOk();
        $response->assertSee('Solicitar cita de ajuste');
        $response->assertSee($envio->codigo_envio);
    }

    public function test_formulario_publico_de_cita_ajuste_devuelve_404_si_token_no_existe(): void
    {
        $response = $this->get(route('inventario.cita-ajuste.mostrar', ['token' => 'invalido']));

        $response->assertNotFound();
    }

    public function test_solicitud_publica_de_cita_ajuste_se_persiste_correctamente(): void
    {
        $envio = $this->crearEnvioPorUbicacion();

        $response = $this->post(route('inventario.cita-ajuste.guardar', ['token' => $envio->token]), [
            'solicitante_nombre' => 'Docente Aula A404',
            'tipo_solicitud' => 'salida_items',
            'medio_contacto' => 'whatsapp',
            'franja_horaria' => 'Jueves 9:00 - 10:00',
            'detalle' => 'Se requiere revisar salida de 4 sillas para mantenimiento.',
            'confirmado_coordinacion' => '1',
        ]);

        $response
            ->assertRedirect(route('inventario.cita-ajuste.mostrar', ['token' => $envio->token]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('solicitudes_ajuste_inventario', [
            'envio_inventario_id' => $envio->id,
            'responsable_id' => $envio->responsable_id,
            'ubicacion_id' => $envio->ubicacion_id,
            'tipo_solicitud' => 'salida_items',
            'estado' => 'pendiente',
            'solicitante_nombre' => 'Docente Aula A404',
            'medio_contacto' => 'whatsapp',
            'contacto_detalle' => $envio->responsable->telefono,
            'franja_horaria' => 'Jueves 9:00 - 10:00',
            'detalle' => 'Se requiere revisar salida de 4 sillas para mantenimiento.',
            'confirmado_coordinacion' => true,
        ]);
    }

    public function test_solicitud_publica_guardada_con_medio_correo_usa_correo_registrado(): void
    {
        $envio = $this->crearEnvioPorUbicacion();

        $this->post(route('inventario.cita-ajuste.guardar', ['token' => $envio->token]), [
            'solicitante_nombre' => 'Docente Aula A404',
            'tipo_solicitud' => 'ajuste_general',
            'medio_contacto' => 'correo',
            'detalle' => 'Revisión de actualización de disponibilidad.',
            'confirmado_coordinacion' => '1',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('solicitudes_ajuste_inventario', [
            'envio_inventario_id' => $envio->id,
            'medio_contacto' => 'correo',
            'contacto_detalle' => $envio->responsable->email,
        ]);
    }

    public function test_solicitud_publica_con_whatsapp_sin_telefono_registrado_pide_whatsapp_manual(): void
    {
        $envio = $this->crearEnvioPorUbicacion();
        $envio->responsable()->update(['telefono' => null]);
        $envio->refresh();

        $this->post(route('inventario.cita-ajuste.guardar', ['token' => $envio->token]), [
            'solicitante_nombre' => 'Docente Aula A404',
            'tipo_solicitud' => 'ajuste_general',
            'medio_contacto' => 'whatsapp',
            'detalle' => 'Revisión de actualización de disponibilidad.',
            'confirmado_coordinacion' => '1',
        ])->assertSessionHasErrors('whatsapp_manual');

        $this->post(route('inventario.cita-ajuste.guardar', ['token' => $envio->token]), [
            'solicitante_nombre' => 'Docente Aula A404',
            'tipo_solicitud' => 'ajuste_general',
            'medio_contacto' => 'whatsapp',
            'whatsapp_manual' => '3009991122',
            'detalle' => 'Revisión de actualización de disponibilidad.',
            'confirmado_coordinacion' => '1',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('solicitudes_ajuste_inventario', [
            'envio_inventario_id' => $envio->id,
            'medio_contacto' => 'whatsapp',
            'contacto_detalle' => '3009991122',
        ]);
    }

    public function test_email_renderiza_boton_e_hipervinculo_para_solicitar_cita_de_ajuste(): void
    {
        $url = 'https://inventario.ediprofe.com/inventario/cita-ajuste/token123';
        $mail = new InventarioReportMail(
            destinatario: 'Docente',
            tipoReporte: 'Inventario por Ubicación',
            nombreReporte: 'A404',
            archivoPath: '/tmp/reporte.pdf',
            archivoNombre: 'reporte.pdf',
            urlCitaAjuste: $url,
        );

        $html = $mail->render();

        $this->assertStringContainsString('Solicitar cita de ajuste', $html);
        $this->assertStringContainsString($url, $html);
        $this->assertStringNotContainsString('class="cta-link"', $html);
    }

    private function crearEnvioPorUbicacion(): EnvioInventario
    {
        $responsable = Responsable::factory()->create();
        $ubicacion = Ubicacion::factory()->create([
            'sede_id' => $responsable->sede_id,
            'responsable_id' => $responsable->id,
        ]);

        return EnvioInventario::factory()->create([
            'responsable_id' => $responsable->id,
            'ubicacion_id' => $ubicacion->id,
            'tipo' => 'por_ubicacion',
            'email_enviado_a' => $responsable->email,
        ]);
    }
}
