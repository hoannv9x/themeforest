<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiSubscription;
use Illuminate\Http\Request;

class ApiSubscriptionController extends Controller
{
    public function mySubscription(Request $request)
    {
        $subscription = ApiSubscription::where('user_id', $request->user()->id)
            ->latest('expires_at')
            ->first();

        return response()->json($subscription);
    }
}
