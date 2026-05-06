<?php

namespace App\Http\Middleware;

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
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!$user->isVip()) {
            $vipStatus = $user->getVipStatus();
            
            if ($user->hasUsedTrial() && !$user->isTrialActive()) {
                return response()->json([
                    'error' => 'VIP trial has expired',
                    'vip_status' => $vipStatus,
                    'message' => 'Your 3-day VIP trial has ended. Upgrade to continue enjoying VIP features.'
                ], 403);
            }

            return response()->json([
                'error' => 'User is not VIP',
                'vip_status' => $vipStatus,
                'message' => 'This feature requires VIP access.'
            ], 403);
        }

        return $next($request);
    }
}
