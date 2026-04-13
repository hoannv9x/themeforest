<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Result;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        return Result::query()
            ->when($request->date, fn($q) => $q->whereDate('date', $request->date))
            ->when($request->region, fn($q) => $q->where('region', $request->region))
            ->latest()
            ->paginate(20);
    }

    public function show(Request $request, string $date)
    {
        return $this->success();
    }
}
