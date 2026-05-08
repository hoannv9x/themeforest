<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Services\ResultService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    protected $resultService;

    public function __construct(ResultService $resultService)
    {
        $this->resultService = $resultService;
    }

    public function index(Request $request)
    {
        return $this->resultService->index($request);
    }

    public function show(Request $request, string $date)
    {
        return $this->resultService->show($request, $date);
    }

    public function vipIndex(Request $request)
    {
        return $this->resultService->index($request, true);
    }

    public function vipShow(Request $request, string $date)
    {
        return $this->resultService->show($request, $date, true);
    }
}
