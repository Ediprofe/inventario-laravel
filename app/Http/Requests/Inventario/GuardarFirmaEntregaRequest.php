<?php

namespace App\Http\Requests\Inventario;

use Illuminate\Foundation\Http\FormRequest;

class GuardarFirmaEntregaRequest extends FormRequest
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
            'firma_data' => ['required', 'string', 'starts_with:data:image/png;base64,', 'max:5000000'],
        ];
    }
}
