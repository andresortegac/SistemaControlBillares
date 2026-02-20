<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:150',
        ];
    }
}

