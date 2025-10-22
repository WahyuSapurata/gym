<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProdukRequest extends FormRequest
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
            'nama_produk' => 'required',
            'harga' => 'required',
            'stok' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'nama_produk.required' => 'Kolom nama produk harus di isi.',
            'harga.required' => 'Kolom harga harus di isi.',
            'stok.required' => 'Kolom stok harus di isi.',
        ];
    }
}
