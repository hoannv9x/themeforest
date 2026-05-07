<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function login(Request $request)
    {
        $payload = $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        $clientId = env('GOOGLE_CLIENT_ID');
        if (!$clientId) {
            return response()->json(['message' => 'GOOGLE_CLIENT_ID is not configured'], 500);
        }

        $tokenInfoResponse = Http::timeout(8)->get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $payload['id_token'],
        ]);

        if (!$tokenInfoResponse->ok()) {
            return response()->json(['message' => 'Invalid Google token'], 422);
        }

        $tokenInfo = $tokenInfoResponse->json();

        $aud = $tokenInfo['aud'] ?? null;
        if ($aud !== $clientId) {
            return response()->json(['message' => 'Invalid token audience'], 422);
        }

        $email = $tokenInfo['email'] ?? null;
        if (!$email) {
            return response()->json(['message' => 'Google token missing email'], 422);
        }

        $emailVerified = $tokenInfo['email_verified'] ?? null;
        if ($emailVerified !== true && $emailVerified !== 'true') {
            return response()->json(['message' => 'Google email not verified'], 422);
        }

        $googleId = $tokenInfo['sub'] ?? null;
        $name = $tokenInfo['name'] ?? null;
        $picture = $tokenInfo['picture'] ?? null;

        $user = User::where('email', $email)->first();
        $isNewUser = false;

        if (!$user) {
            $isNewUser = true;
            $user = User::create([
                'name' => $name ?: Str::before($email, '@'),
                'email' => $email,
                'google_id' => $googleId,
                'avatar_url' => $picture,
                'password' => Str::password(32),
            ]);
            $user->startVipTrial();
            $user->refresh();
        } else {
            $dirty = false;

            if ($googleId && $user->google_id !== $googleId) {
                $user->google_id = $googleId;
                $dirty = true;
            }

            if ($picture && $user->avatar_url !== $picture) {
                $user->avatar_url = $picture;
                $dirty = true;
            }

            if ($name && $user->name !== $name) {
                $user->name = $name;
                $dirty = true;
            }

            if ($dirty) {
                $user->save();
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => $isNewUser ? 'Google registration successful' : 'Google login successful',
            'user' => $user,
            'token' => $token,
            'vip_status' => $user->getVipStatus(),
        ]);
    }
}

