<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsumoMesaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'producto_id' => 'nullable|exists:productos,id',
            'cantidad' => 'nullable|integer|min:1',
            'descripcion' => 'required_without:producto_id|string|max:150',
            'monto' => 'required_without:producto_id|numeric|min:0.01',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('producto_id') && !$this->filled('cantidad')) {
            $this->merge(['cantidad' => 1]);
        }
    }
}
