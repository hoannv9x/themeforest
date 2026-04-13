<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public function today()
    {
        return Prediction::select('numbers', 'meta', 'region')->whereDate('date', today())->first();
    }
}
