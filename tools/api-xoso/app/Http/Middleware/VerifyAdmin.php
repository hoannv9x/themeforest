<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class VerifyAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $isAdmin = $user->role === User::ROLE_ADMIN;
        $isDeveloper = $user->permission === User::PERMISSION_DEVELOPER;
        if (!$isAdmin && !$isDeveloper) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
