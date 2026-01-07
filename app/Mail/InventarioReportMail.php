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
    protected string $archivoPath;
    protected string $archivoNombre;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $destinatario,
        string $tipoReporte,
        string $nombreReporte,
        string $archivoPath,
        string $archivoNombre
    ) {
        $this->destinatario = $destinatario;
        $this->tipoReporte = $tipoReporte;
        $this->nombreReporte = $nombreReporte;
        $this->archivoPath = $archivoPath;
        $this->archivoNombre = $archivoNombre;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reporte de Inventario - {$this->nombreReporte}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.inventario-report',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->archivoPath)
                ->as($this->archivoNombre),
        ];
    }
}
