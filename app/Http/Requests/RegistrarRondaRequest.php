<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarRondaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'perdedores' => 'required|array|min:1',
            'perdedores.*' => 'integer|distinct',
            'detalle' => 'nullable|string|max:150',
        ];
    }
}

