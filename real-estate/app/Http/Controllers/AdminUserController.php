<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Password::defaults()],
            'role' => 'required|string|in:admin,user,agent',
            'avatar' => 'nullable|string', // This will be the relative path
            'phone_number' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => $validated['role'] === 'admin',
            'profile_picture' => $validated['avatar'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        // If we have a temporary avatar, move it to the user's folder
        if ($user->profile_picture && str_contains($user->profile_picture, 'avatars/temp/')) {
            $newPath = str_replace('avatars/temp/', 'avatars/' . $user->id . '/', $user->profile_picture);
            if (Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->move($user->profile_picture, $newPath);
                $user->profile_picture = $newPath;
                $user->save();
            }
        }

        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['sometimes', Password::defaults()],
            'role' => 'sometimes|string|in:admin,user,agent',
            'avatar' => 'nullable|string', // Relative path
            'phone_number' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        if (isset($validated['name'])) $user->name = $validated['name'];
        if (isset($validated['email'])) $user->email = $validated['email'];
        if (isset($validated['password']) && $validated['password']) {
            $user->password = Hash::make($validated['password']);
        }
        if (isset($validated['role'])) $user->is_admin = ($validated['role'] === 'admin');

        if (isset($validated['avatar'])) {
            // If the avatar path is different from the old one, delete the old file
            if ($user->profile_picture && $user->profile_picture !== $validated['avatar']) {
                if (Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
            }
            $user->profile_picture = $validated['avatar'];
        }

        if (isset($validated['phone_number'])) $user->phone_number = $validated['phone_number'];
        if (isset($validated['address'])) $user->address = $validated['address'];

        $user->save();

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        
        // Delete avatar if exists
        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}
