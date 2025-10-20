<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransaksiRequest extends FormRequest
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
            'uuid_member' => 'required',
            'uuid_paket' => 'required',
            'jenis_pembayaran' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'uuid_member.required' => 'Kolom nama member harus di isi.',
            'uuid_paket.required' => 'Kolom nama paket harus di isi.',
            'jenis_pembayaran.required' => 'Kolom jenis pembayaran harus di isi.',
        ];
    }
}
