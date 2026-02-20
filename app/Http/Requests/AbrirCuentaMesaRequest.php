<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AbrirCuentaMesaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notas' => 'nullable|string|max:500',
        ];
    }
}

