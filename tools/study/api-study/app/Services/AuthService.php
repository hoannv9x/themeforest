<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'level' => 'beginner',
        ]);

        return $user;
    }

    public function login(string $email, string $password)
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    public function updateProfile(User $user, array $data)
    {
        $user->update(['name' => $data['name']]);

        if (isset($data['level']) || isset($data['goal'])) {
            $user->profile()->update([
                'level' => $data['level'] ?? $user->profile->level,
                'goal' => $data['goal'] ?? $user->profile->goal,
            ]);
        }

        return $user->load('profile');
    }
}
