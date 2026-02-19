<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InventarioReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $destinatario;

    public string $tipoReporte;

    public string $nombreReporte;

    public ?string $urlAprobacion;

    public ?string $codigoEnvio;

    public ?string $firmanteNombre;

    public ?string $firmaResponsableBase64;

    public ?string $firmaEntregaNombre;

    public ?string $firmaEntregaCargo;

    public ?string $firmaEntregaBase64;

    public ?string $urlCitaAjuste;

    /** @var array<array{path: string, name: string}> */
    protected array $archivos;

    public function __construct(
        string $destinatario,
        string $tipoReporte,
        string $nombreReporte,
        string|array $archivoPath,
        string|array $archivoNombre,
        ?string $urlAprobacion = null,
        ?string $codigoEnvio = null,
        ?string $firmanteNombre = null,
        ?string $firmaResponsableBase64 = null,
        ?string $firmaEntregaNombre = null,
        ?string $firmaEntregaCargo = null,
        ?string $firmaEntregaBase64 = null,
        ?string $urlCitaAjuste = null,
    ) {
        $this->destinatario = $destinatario;
        $this->tipoReporte = $tipoReporte;
        $this->nombreReporte = $nombreReporte;
        $this->urlAprobacion = $urlAprobacion;
        $this->codigoEnvio = $codigoEnvio;
        $this->firmanteNombre = $firmanteNombre;
        $this->firmaResponsableBase64 = $firmaResponsableBase64;
        $this->firmaEntregaNombre = $firmaEntregaNombre;
        $this->firmaEntregaCargo = $firmaEntregaCargo;
        $this->firmaEntregaBase64 = $firmaEntregaBase64;
        $this->urlCitaAjuste = $urlCitaAjuste;

        // Normalize to array of files for multi-attachment support
        if (is_array($archivoPath)) {
            $nombres = is_array($archivoNombre) ? $archivoNombre : [$archivoNombre];
            $this->archivos = [];
            foreach ($archivoPath as $i => $path) {
                $this->archivos[] = [
                    'path' => $path,
                    'name' => $nombres[$i] ?? basename($path),
                ];
            }
        } else {
            $this->archivos = [
                ['path' => $archivoPath, 'name' => $archivoNombre],
            ];
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(
                config('mail.from.address'),
                'Inventario San José'
            ),
            subject: "Reporte de Inventario - {$this->nombreReporte}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inventario-report',
        );
    }

    public function attachments(): array
    {
        return collect($this->archivos)
            ->map(fn ($archivo) => Attachment::fromPath($archivo['path'])->as($archivo['name']))
            ->toArray();
    }
}
