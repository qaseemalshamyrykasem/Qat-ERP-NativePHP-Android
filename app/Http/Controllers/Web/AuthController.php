<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(protected AuthService $auth) {}

    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $result = $this->auth->login($credentials['username'], $credentials['password']);
        if (! $result['success']) {
            return back()->with('error', $result['message'])->withInput($request->only('username'));
        }
        return redirect()->intended(route('dashboard'));
    }

    public function logout()
    {
        $this->auth->logout();
        return redirect()->route('login');
    }
}
