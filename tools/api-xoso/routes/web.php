<?php
use Illuminate\Support\Facades\Route;

Route::get('/session-test', function () {
  session(['count' => session('count', 0) + 1]);

  return [
    'count' => session('count'),
    'session_id' => session()->getId(),
  ];
});
