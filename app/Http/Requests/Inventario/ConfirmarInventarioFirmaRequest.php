<?php

namespace App\Http\Requests\Inventario;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmarInventarioFirmaRequest extends FormRequest
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
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'firmante_nombre' => ['required', 'string', 'max:120'],
            'firma_data' => ['required', 'string', 'starts_with:data:image/png;base64,', 'max:5000000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $observaciones = $this->input('observaciones');

        $this->merge([
            'firmante_nombre' => trim((string) $this->input('firmante_nombre', '')),
            'observaciones' => is_string($observaciones) ? trim($observaciones) : $observaciones,
        ]);
    }
}
