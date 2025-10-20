<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaketRequest extends FormRequest
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
            'tipe_member' => 'required',
            'nama_paket' => 'required',
            'harga' => 'required',
            'status' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'tipe_member.required' => 'Kolom tipe member harus di isi.',
            'nama_paket.required' => 'Kolom nama paket harus di isi.',
            'harga.required' => 'Kolom harga harus di isi.',
            'status.required' => 'Kolom status harus di isi.',
        ];
    }
}
