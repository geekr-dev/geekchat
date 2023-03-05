<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $messages = collect(session('messages', []))->reject(fn ($message) => $message['role'] === 'system');

    return view('welcome', [
        'messages' => $messages
    ]);
});

Route::post('/chat', ChatController::class . '@chat');

Route::get('/reset', ChatController::class . '@reset');
