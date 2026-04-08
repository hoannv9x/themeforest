<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboardStats()
    {
        $totalUsers = User::count();
        $totalProperties = Property::count();
        $pendingProperties = Property::where('status', 'pending')->count();

        return response()->json([
            'totalUsers' => $totalUsers,
            'totalProperties' => $totalProperties,
            'pendingProperties' => $pendingProperties,
        ]);
    }
}
