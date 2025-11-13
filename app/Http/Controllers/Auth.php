<?php

namespace App\Http\Controllers;

use App\Http\Requests\Register;
use App\Http\Requests\RequestAuth;
use App\Models\Member;
use App\Models\Paket;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Auth extends BaseController
{
    public function show()
    {
        $module = 'Login';
        return view('auth.login', compact('module'));
    }

    public function login_proses(RequestAuth $authRequest)
    {
        $credential = $authRequest->getCredentials();

        if (!FacadesAuth::attempt($credential)) {
            return redirect()->route('login.login-akun')->with('failed', 'Username atau Password salah')->withInput($authRequest->only('username'));
        } else {
            return $this->authenticated();
        }
    }

    public function authenticated()
    {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard-admin');
        }
    }

    public function logout()
    {
        FacadesAuth::logout();
        return redirect()->route('login.login-akun')->with('success', 'Berhasil Logout');
    }

    // API

    public function registrasi(Register $register)
    {
        DB::beginTransaction();

        try {
            // === 1. Upload foto member (jika ada) ===
            $path = null;
            if ($register->hasFile('foto_member')) {
                $fileName = time() . '_' . uniqid() . '.' . $register->foto_member->extension();
                $path = $register->foto_member->storeAs('foto_member', $fileName, 'public');
            }

            // === 2. Buat user baru ===
            $user = User::create([
                'nama'          => $register->nama,
                'username'      => $register->username,
                'password'      => Hash::make($register->password_hash),
                'password_hash' => $register->password_hash,
                'role'          => 'member',
            ]);

            // === 3. Buat data member ===
            $member = Member::create([
                'uuid_user'     => $user->uuid,
                'jenis_kelamin' => $register->jenis_kelamin,
                'tanggal_lahir' => $register->tanggal_lahir,
                'alamat'        => $register->alamat,
                'berat_badan'   => $register->berat_badan,
                'tinggi_badan'  => $register->tinggi_badan,
                'tgl_registrasi' => now()->format('d-m-Y'),
                'nomor_telepon' => $register->nomor_telepon,
                'foto_member'   => $path,
            ]);

            // === 4. Upload bukti pembayaran (jika ada) ===
            $path_bukti = null;
            if ($register->hasFile('bukti')) {
                $fileName = time() . '_' . uniqid() . '.' . $register->bukti->extension();
                $path_bukti = $register->bukti->storeAs('bukti', $fileName, 'public');
            }

            // === 5. Ambil data paket ===
            $paket = Paket::where('uuid', $register->uuid_paket)->firstOrFail();

            // === 6. Hitung tanggal mulai & berakhir ===
            $startDate = Carbon::parse($member->tgl_registrasi);

            if (!is_null($paket->durasi_hari) && $paket->durasi_hari > 0) {
                $endDate = $startDate->copy()->addDays($paket->durasi_hari);
            } else {
                // Jika paket berbasis sesi → masa aktif default 60 hari
                $endDate = $startDate->copy()->addDays(60);
            }

            $startDateFormatted = $startDate->format('d-m-Y');
            $endDateFormatted   = $endDate->format('d-m-Y');

            // === 7. Generate nomor invoice ===
            $no_invoice = 'INV-' . strtoupper(Str::random(6)) . '-' . date('dmY');

            // === 8. Buat transaksi ===
            $transaction = Transaksi::create([
                'uuid_member'       => $member->uuid,
                'uuid_paket'        => $paket->uuid,
                'tipe_member'       => $paket->tipe_member,
                'no_invoice'        => $no_invoice,
                'jenis_pembayaran'  => $register->jenis_pembayaran,
                'total_bayar'       => $paket->harga,
                'tanggal_mulai'     => $startDateFormatted,
                'tanggal_selesai'   => $endDateFormatted,
                'remaining_session' => $paket->total_sesi ?? 0,
                'status'            => 'paid',
                'keterangan'        => $register->keterangan ?? null,
                'bukti'             => $path_bukti,
            ]);

            // === 9. Update data member ===
            $member->update([
                'expired_at'     => $endDateFormatted,
                'tipe_member'    => $paket->tipe_member,
                'status_member'  => 'active',
            ]);

            // === 10. Commit transaksi ===
            DB::commit();

            // === 11. Return response sukses ===
            return response()->json([
                'status'  => true,
                'message' => 'Registrasi berhasil!',
                'data'    => [
                    'user'        => $user,
                    'member'      => $member,
                    'transaksi'   => $transaction,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            // === Jika gagal, kembalikan error ===
            return response()->json([
                'status'  => false,
                'message' => 'Registrasi gagal!',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function do_login(RequestAuth $authRequest)
    {
        $user = User::where('username', $authRequest->username)->first();
        if ($user->role === 'member') {
            $member = $user->members;
            if ($member->status_member == null) {
                return $this->sendError('Unauthorised.', ['error' => 'Akun belum di verifikasi Admin'], 401);
            }
        }

        $credential = $authRequest->getCredentials();
        if (FacadesAuth::attempt($credential)) {
            $token = $authRequest->user()->createToken('tokenAPI')->plainTextToken;
            $data = [
                'token' => $token,
                'user' => $user,
                'member' => $user->members ? $user->members : null,
            ];

            return $this->sendResponse($data, 'Berhasil login.');
        } else {
            return $this->sendError('Unauthorised.', ['error' => 'Username atau Password Salah'], 401);
        }
    }

    public function revoke(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse('Success', 'Berhasil logout');
    }
}
