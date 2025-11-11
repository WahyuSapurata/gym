<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClasRequest extends FormRequest
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
            'uuid_instruktur' => 'required',
            'nama_clas' => 'required',
            'harga' => 'required',
            'kategori' => 'required',
            'jadwal' => 'required',
            'durasi' => 'required',
            'slot' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'uuid_instruktur.required' => 'Kolom instruktur harus di isi.',
            'nama_clas.required' => 'Kolom nama class harus di isi.',
            'harga.required' => 'Kolom harga harus di isi.',
            'kategori.required' => 'Kolom kategori harus di isi.',
            'jadwal.required' => 'Kolom jadwal harus di isi.',
            'durasi.required' => 'Kolom durasi harus di isi.',
            'slot.required' => 'Kolom slot harus di isi.',
        ];
    }
}
