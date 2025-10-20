<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestAuth;
use Illuminate\Support\Facades\Auth as FacadesAuth;

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
}
