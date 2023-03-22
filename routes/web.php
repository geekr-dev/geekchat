<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', ChatController::class . '@index')->name('home');
Route::get('/messages', ChatController::class . '@messages')->name('messages');
Route::post('/chat', ChatController::class . '@chat')->name('chat')->middleware('throttle:chat');
Route::post('/translate', ChatController::class . '@translate')->name('translate')->middleware('throttle:chat');
Route::get('/stream', ChatController::class . '@stream')->name('stream');
Route::post('/valid', ChatController::class . '@valid')->name('valid');
Route::post('/audio', ChatController::class . '@audio')->name('audio')->middleware('throttle:audio');
Route::post('/image', ChatController::class . '@image')->name('image')->middleware('throttle:imgae');
Route::get('/reset', ChatController::class . '@reset')->name('reset');

// 暂不开放用户认证功能
// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// require __DIR__ . '/auth.php';
