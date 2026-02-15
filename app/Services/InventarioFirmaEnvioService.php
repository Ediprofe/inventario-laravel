<?php

namespace App\Services;

use App\Exports\Responsable\ResponsableIndividualExport;
use App\Exports\Ubicacion\UbicacionIndividualExport;
use App\Mail\InventarioReportMail;
use App\Models\EnvioInventario;
use App\Models\Responsable;
use App\Models\Ubicacion;
use App\Support\DompdfRuntimeConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Throwable;

class InventarioFirmaEnvioService
{
    public function __construct(
        protected InventarioReportService $reportService,
    ) {
    }

    public function buildApprovalUrl(string $token): string
    {
        $baseUrl = rtrim((string) config('app.public_url', config('app.url')), '/');

        return $baseUrl . "/inventario/aprobar/{$token}";
    }

    public function crearEnvioBorradorPorUbicacion(Ubicacion $ubicacion): EnvioInventario
    {
        $responsable = $ubicacion->responsable;

        if (!$responsable) {
            throw new RuntimeException('Esta ubicación no tiene un responsable asignado.');
        }

        if (!$responsable->email) {
            throw new RuntimeException("El responsable {$responsable->nombre_completo} no tiene email registrado.");
        }

        return EnvioInventario::create([
            'responsable_id' => $responsable->id,
            'tipo' => 'por_ubicacion',
            'ubicacion_id' => $ubicacion->id,
            'email_enviado_a' => $responsable->email,
            'enviado_at' => now(),
            'token' => EnvioInventario::generarToken(),
        ]);
    }

    public function crearEnvioBorradorPorResponsable(Responsable $responsable): EnvioInventario
    {
        if (!$responsable->email) {
            throw new RuntimeException("El responsable {$responsable->nombre_completo} no tiene email registrado.");
        }

        return EnvioInventario::create([
            'responsable_id' => $responsable->id,
            'tipo' => 'por_responsable',
            'ubicacion_id' => null,
            'email_enviado_a' => $responsable->email,
            'enviado_at' => now(),
            'token' => EnvioInventario::generarToken(),
        ]);
    }

    /**
     * @return array{email:string,codigo_envio:string,pdf:string,excel:string}
     */
    public function enviarInventarioFirmado(EnvioInventario $envio): array
    {
        $envio->loadMissing(['responsable', 'ubicacion.sede']);

        if (!$envio->firmante_nombre || !$envio->firma_base64) {
            throw new RuntimeException('No es posible enviar: falta la firma del responsable.');
        }

        $localDisk = Storage::disk('local');
        if (!$localDisk->exists('temp')) {
            $localDisk->makeDirectory('temp');
        }

        $firmaEntrega = $this->getFirmaEntregaData();
        $meta = $this->buildExportMeta($envio, $firmaEntrega);
        $fecha = now()->format('Y-m-d_His');

        if ($envio->tipo === 'por_ubicacion') {
            if (!$envio->ubicacion_id) {
                throw new RuntimeException('El envío por ubicación no tiene ubicación asociada.');
            }

            $data = $this->reportService->getInventarioPorUbicacion($envio->ubicacion_id);
            if (!$data['ubicacion']) {
                throw new RuntimeException('Ubicación no encontrada para generar el reporte.');
            }

            $view = 'pdf.ubicacion';
            $descriptor = ($data['ubicacion']->codigo ?? 'UBI') . '_' . $fecha;
            $excelExport = new UbicacionIndividualExport($envio->ubicacion_id, $meta);
        } else {
            $data = $this->reportService->getInventarioPorResponsable($envio->responsable_id);
            if (!$data['responsable']) {
                throw new RuntimeException('Responsable no encontrado para generar el reporte.');
            }

            $view = 'pdf.responsable';
            $nombreLimpio = str_replace(' ', '_', $data['responsable']->nombre_completo);
            $descriptor = $nombreLimpio . '_' . $fecha;
            $excelExport = new ResponsableIndividualExport($envio->responsable_id, $meta);
        }

        $pdfFilename = "Inventario_{$envio->codigo_envio}_{$descriptor}.pdf";
        $excelFilename = "Inventario_{$envio->codigo_envio}_{$descriptor}.xlsx";
        $pdfPath = $localDisk->path('temp/' . $pdfFilename);
        $excelPath = $localDisk->path('temp/' . $excelFilename);

        try {
            DompdfRuntimeConfig::apply();
            Pdf::loadView($view, [
                'data' => $data,
                'envio' => $envio,
                'firmaEntrega' => $firmaEntrega,
            ])->save($pdfPath);

            Excel::store($excelExport, 'temp/' . $excelFilename, 'local');

            Mail::to($envio->email_enviado_a)->send(new InventarioReportMail(
                destinatario: $envio->responsable?->nombre_completo ?? 'Responsable',
                tipoReporte: $envio->tipo === 'por_ubicacion' ? 'Inventario por Ubicación' : 'Inventario por Responsable',
                nombreReporte: $envio->tipo === 'por_ubicacion'
                    ? (($envio->ubicacion?->codigo ?? '') . ' - ' . ($envio->ubicacion?->nombre ?? 'Ubicación'))
                    : ($envio->responsable?->nombre_completo ?? 'Responsable'),
                archivoPath: [$pdfPath, $excelPath],
                archivoNombre: [$pdfFilename, $excelFilename],
                urlAprobacion: null,
                codigoEnvio: $envio->codigo_envio,
                firmanteNombre: $envio->firmante_nombre,
                firmaResponsableBase64: $envio->firma_base64,
                firmaEntregaNombre: $firmaEntrega['nombre'],
                firmaEntregaCargo: $firmaEntrega['cargo'],
                firmaEntregaBase64: $firmaEntrega['base64'],
            ));

            // Reflect actual email dispatch time.
            $envio->forceFill([
                'enviado_at' => now(),
            ])->save();

            return [
                'email' => $envio->email_enviado_a,
                'codigo_envio' => $envio->codigo_envio,
                'pdf' => $pdfFilename,
                'excel' => $excelFilename,
            ];
        } finally {
            $this->safeUnlink($pdfPath);
            $this->safeUnlink($excelPath);
        }
    }

    /**
     * @return array{nombre:string,cargo:string,base64:?string}
     */
    protected function getFirmaEntregaData(): array
    {
        $responsableEntrega = Responsable::query()
            ->where('es_firmante_entrega', true)
            ->orderByDesc('updated_at')
            ->first();

        if ($responsableEntrega) {
            return [
                'nombre' => $responsableEntrega->nombre_completo,
                'cargo' => (string) ($responsableEntrega->cargo ?? ''),
                'base64' => $this->dataUriFromPublicStoragePath($responsableEntrega->firma_entrega_path),
            ];
        }

        $nombre = trim((string) config('institucion.firma_entrega_nombre', ''));
        $cargo = trim((string) config('institucion.firma_entrega_cargo', ''));
        $relativePath = trim((string) config('institucion.firma_entrega_imagen', ''));

        return [
            'nombre' => $nombre !== '' ? $nombre : 'Encargado de inventario',
            'cargo' => $cargo,
            'base64' => $this->dataUriFromAbsolutePath(
                $relativePath !== '' ? public_path(ltrim($relativePath, '/')) : null
            ),
        ];
    }

    /**
     * @param array{nombre:string,cargo:string,base64:?string} $firmaEntrega
     * @return array<string,string>
     */
    protected function buildExportMeta(EnvioInventario $envio, array $firmaEntrega): array
    {
        return [
            'codigo_envio' => $envio->codigo_envio,
            'firmante_responsable' => $envio->firmante_nombre ?? '',
            'firmante_entrega' => $firmaEntrega['nombre'] ?? '',
            'firmante_entrega_cargo' => $firmaEntrega['cargo'] ?? '',
            'fecha_firma' => optional($envio->aprobado_at)->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i'),
        ];
    }

    protected function safeUnlink(string $path): void
    {
        try {
            if (is_file($path)) {
                @unlink($path);
            }
        } catch (Throwable) {
            // Ignore temp cleanup errors.
        }
    }

    protected function dataUriFromPublicStoragePath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        return $this->dataUriFromAbsolutePath(Storage::disk('public')->path($path));
    }

    protected function dataUriFromAbsolutePath(?string $absolutePath): ?string
    {
        if (!$absolutePath || !is_file($absolutePath)) {
            return null;
        }

        $mime = mime_content_type($absolutePath) ?: 'image/png';
        $content = file_get_contents($absolutePath);
        if ($content === false) {
            return null;
        }

        return 'data:' . $mime . ';base64,' . base64_encode($content);
    }
}
