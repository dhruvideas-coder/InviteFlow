<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception) {
            return redirect('/login?error=google_auth_failed');
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            return redirect('/login?error=email_not_found');
        }

        $user->update([
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        if (!$user->is_active) {
            return redirect('/login?error=account_suspended');
        }

        Auth::login($user);

        return redirect('/dashboard');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/login');
    }
}
