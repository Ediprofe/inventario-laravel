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
    protected string $archivoPath;
    protected string $archivoNombre;

    public function __construct(
        string $destinatario,
        string $tipoReporte,
        string $nombreReporte,
        string $archivoPath,
        string $archivoNombre,
        ?string $urlAprobacion = null
    ) {
        $this->destinatario = $destinatario;
        $this->tipoReporte = $tipoReporte;
        $this->nombreReporte = $nombreReporte;
        $this->archivoPath = $archivoPath;
        $this->archivoNombre = $archivoNombre;
        $this->urlAprobacion = $urlAprobacion;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
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
        return [
            Attachment::fromPath($this->archivoPath)
                ->as($this->archivoNombre),
        ];
    }
}
