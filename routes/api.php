<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\UploadTrainingData;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/chatbot/info', [ChatBotController::class, 'index'])->name('chatbot.info');

Route::post('/chatbot/chat', [ChatBotController::class, 'chat'])->name('chatbot.chat');

Route::match(['get', 'post'],'/tg-chatbot/chat', [ChatBotController::class, 'tgChat'])->name('chatbot.tgChat');


Route::post('/insert/chunks', [UploadTrainingData::class, 'chunkStorer'])->name('chunkStorer');
Route::post('/update/history', [UploadTrainingData::class, 'historyStatusUpdater'])->name('historyStatusUpdater');
