<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
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
            'nama' => 'required',
            'username' => 'required',
            'password_hash' => 'required',
            'jenis_kelamin' => 'required',
            'tanggal_lahir' => 'required',
            'berat_badan' => 'required',
            'alamat' => 'required',
            'tinggi_badan' => 'required',
            'tgl_registrasi' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'nama.required' => 'Kolom nama harus di isi.',
            'username.required' => 'Kolom username harus di isi.',
            'password_hash.required' => 'Kolom password harus di isi.',
            'jenis_kelamin.required' => 'Kolom jenis kelamin harus di isi.',
            'tanggal_lahir.required' => 'Kolom tanggal lahir harus di isi.',
            'berat_badan.required' => 'Kolom berat badan harus di isi.',
            'alamat.required' => 'Kolom alamat harus di isi.',
            'tinggi_badan.required' => 'Kolom tinggi badan harus di isi.',
            'tipe_member.required' => 'Kolom tipe member harus di isi.',
            'status_member.required' => 'Kolom status member harus di isi.',
            'tgl_registrasi.required' => 'Kolom tgl registrasi harus di isi.',
        ];
    }
}
