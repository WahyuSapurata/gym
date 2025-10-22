<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperasionalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'deskripsi' => 'required',
            'biaya_operasional' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'deskripsi.required' => 'Kolom deskripsi harus di isi.',
            'biaya_operasional.required' => 'Kolom biaya operasional harus di isi.',
        ];
    }
}
