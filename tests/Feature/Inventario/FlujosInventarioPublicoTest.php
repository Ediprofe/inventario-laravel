<?php

namespace Tests\Feature\Inventario;

use App\Models\EnvioInventario;
use App\Models\Responsable;
use App\Models\Sede;
use App\Models\Ubicacion;
use App\Models\User;
use App\Services\InventarioFirmaEnvioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class FlujosInventarioPublicoTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_signing_link_generation_requires_authentication(): void
    {
        $ubicacion = $this->crearUbicacionConResponsable();

        $response = $this->postJson(route('reportes.pdf.ubicacion.enviar', ['ubicacionId' => $ubicacion->id]));

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_generate_signing_link_for_location_report(): void
    {
        $user = User::factory()->create();
        $ubicacion = $this->crearUbicacionConResponsable();

        $response = $this
            ->actingAs($user)
            ->postJson(route('reportes.pdf.ubicacion.enviar', ['ubicacionId' => $ubicacion->id]));

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('email_destino', $ubicacion->responsable->email);

        $this->assertDatabaseHas('envios_inventario', [
            'tipo' => 'por_ubicacion',
            'ubicacion_id' => $ubicacion->id,
            'responsable_id' => $ubicacion->responsable_id,
            'email_enviado_a' => $ubicacion->responsable->email,
        ]);
    }

    public function test_authenticated_user_can_generate_signing_link_for_responsable_report(): void
    {
        $user = User::factory()->create();
        $responsable = $this->crearResponsableConSede();

        $response = $this
            ->actingAs($user)
            ->postJson(route('reportes.pdf.responsable.enviar', ['responsableId' => $responsable->id]));

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('email_destino', $responsable->email);

        $this->assertDatabaseHas('envios_inventario', [
            'tipo' => 'por_responsable',
            'ubicacion_id' => null,
            'responsable_id' => $responsable->id,
            'email_enviado_a' => $responsable->email,
        ]);
    }

    public function test_public_inventory_approval_persists_signature_and_marks_record_as_approved(): void
    {
        $envio = $this->crearEnvioPendiente();
        $this->mockInventarioFirmaEnvioService();

        $response = $this
            ->from(route('inventario.aprobar', ['token' => $envio->token]))
            ->post(route('inventario.aprobar.confirmar', ['token' => $envio->token]), [
                'firmante_nombre' => '  Docente Responsable  ',
                'firma_data' => $this->fakePngDataUri(),
                'observaciones' => 'Firmado en reunión de inventario.',
            ]);

        $response
            ->assertRedirect(route('inventario.aprobar', ['token' => $envio->token]))
            ->assertSessionHas('success');

        $envio->refresh();

        $this->assertNotNull($envio->aprobado_at);
        $this->assertSame('Docente Responsable', $envio->firmante_nombre);
        $this->assertSame('Firmado en reunión de inventario.', $envio->observaciones);
        $this->assertStringStartsWith('data:image/png;base64,', (string) $envio->firma_base64);
    }

    public function test_delivery_signature_capture_requires_signed_url(): void
    {
        $responsable = $this->crearResponsableConSede();

        $response = $this->get(route('firma.entrega.capturar', ['responsable' => $responsable->id]));

        $response->assertForbidden();
    }

    public function test_delivery_signature_capture_saves_signature_on_valid_signed_url(): void
    {
        Storage::fake('public');
        $responsable = $this->crearResponsableConSede();

        $signedPostUrl = URL::temporarySignedRoute(
            'firma.entrega.guardar',
            now()->addMinutes(30),
            ['responsable' => $responsable->id],
            absolute: false,
        );

        $response = $this->post($signedPostUrl, [
            'firma_data' => $this->fakePngDataUri(),
        ]);

        $response->assertSessionHas('success');

        $responsable->refresh();
        $this->assertTrue($responsable->es_firmante_entrega);
        $this->assertNotNull($responsable->firma_entrega_path);
        Storage::disk('public')->assertExists($responsable->firma_entrega_path);
    }

    public function test_public_signature_routes_have_custom_rate_limiters(): void
    {
        $this->assertContains(
            'throttle:inventario-aprobacion-view',
            Route::getRoutes()->getByName('inventario.aprobar')->gatherMiddleware(),
        );
        $this->assertContains(
            'throttle:inventario-aprobacion-submit',
            Route::getRoutes()->getByName('inventario.aprobar.confirmar')->gatherMiddleware(),
        );
        $this->assertContains(
            'throttle:firma-entrega-view',
            Route::getRoutes()->getByName('firma.entrega.capturar')->gatherMiddleware(),
        );
        $this->assertContains(
            'throttle:firma-entrega-submit',
            Route::getRoutes()->getByName('firma.entrega.guardar')->gatherMiddleware(),
        );
    }

    private function mockInventarioFirmaEnvioService(): void
    {
        $mock = Mockery::mock(InventarioFirmaEnvioService::class);
        $mock->shouldReceive('enviarInventarioFirmado')
            ->once()
            ->andReturn([
                'email' => 'responsable@example.com',
                'codigo_envio' => 'ENV-000001',
                'pdf' => 'inventario.pdf',
                'excel' => 'inventario.xlsx',
            ]);

        $this->app->instance(InventarioFirmaEnvioService::class, $mock);
    }

    private function crearEnvioPendiente(): EnvioInventario
    {
        $responsable = $this->crearResponsableConSede();

        return EnvioInventario::create([
            'responsable_id' => $responsable->id,
            'tipo' => 'por_responsable',
            'ubicacion_id' => null,
            'email_enviado_a' => $responsable->email,
            'enviado_at' => now(),
            'token' => EnvioInventario::generarToken(),
        ]);
    }

    private function crearUbicacionConResponsable(): Ubicacion
    {
        $responsable = $this->crearResponsableConSede();

        return Ubicacion::create([
            'sede_id' => $responsable->sede_id,
            'nombre' => 'Aula 101',
            'codigo' => 'A101',
            'tipo' => 'aula',
            'responsable_id' => $responsable->id,
            'activo' => true,
        ]);
    }

    private function crearResponsableConSede(): Responsable
    {
        $sede = Sede::create([
            'nombre' => 'Sede Principal',
            'codigo' => 'PRIN-'.uniqid(),
            'activo' => true,
        ]);

        return Responsable::create([
            'nombre' => 'Docente',
            'apellido' => 'Prueba',
            'email' => 'docente.'.uniqid().'@example.com',
            'sede_id' => $sede->id,
            'activo' => true,
        ]);
    }

    private function fakePngDataUri(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO2l9eQAAAAASUVORK5CYII=';
    }
}
