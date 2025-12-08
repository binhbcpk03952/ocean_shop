<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    public function googleRedirect() {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function googleCallback() {
        $socialUser = Socialite::driver('google')->stateless()->user();

        $user = User::updateOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name' => $socialUser->getName(),
                'password' => bcrypt(Str::random(24)),
                'email_verified_at' => now(),
                'role' => 'user'
            ]
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        return redirect("http://127.0.0.1:8000/social-login?token=".$token);
    }

    public function facebookRedirect() {
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    public function facebookCallback() {
        $socialUser = Socialite::driver('facebook')->stateless()->user();

        $user = User::updateOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name' => $socialUser->getName(),
                'password' => bcrypt(Str::random(24)),
                'email_verified_at' => now(),
                'role' => 'user'
            ]
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        return redirect("http://localhost:5173/social-login?token=".$token);
    }
}
