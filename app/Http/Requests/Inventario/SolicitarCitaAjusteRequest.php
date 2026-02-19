<?php

namespace App\Http\Requests\Inventario;

use Illuminate\Foundation\Http\FormRequest;

class SolicitarCitaAjusteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'solicitante_nombre' => ['required', 'string', 'max:120'],
            'tipo_solicitud' => ['required', 'in:ajuste_general,entrada_items,salida_items,baja_items,mantenimiento,otro'],
            'medio_contacto' => ['required', 'in:whatsapp,correo'],
            'whatsapp_manual' => ['nullable', 'string', 'max:30'],
            'franja_horaria' => ['nullable', 'string', 'max:120'],
            'detalle' => ['required', 'string', 'max:3000'],
            'confirmado_coordinacion' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'medio_contacto.required' => 'Seleccione el medio de contacto (WhatsApp o correo).',
            'confirmado_coordinacion.accepted' => 'Debe confirmar la validación previa con coordinación de inventario.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'solicitante_nombre' => trim((string) $this->input('solicitante_nombre', '')),
            'whatsapp_manual' => trim((string) $this->input('whatsapp_manual', '')),
            'franja_horaria' => trim((string) $this->input('franja_horaria', '')),
            'detalle' => trim((string) $this->input('detalle', '')),
        ]);
    }
}
