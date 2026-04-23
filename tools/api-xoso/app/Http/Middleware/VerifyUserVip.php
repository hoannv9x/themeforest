<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyUserVip
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $isVip = $user
            && $user->role === User::ROLE_VIP
            && $user->vip_expired_at
            && $user->vip_expired_at->isFuture();

        if (!$isVip) {
            return response()->json(['error' => 'User is not VIP'], 403);
        }

        return $next($request);
    }
}
