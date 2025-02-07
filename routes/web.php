<?php


use Illuminate\Support\Facades\Route;


use App\Http\Controllers\UploadTrainingData;
use App\Http\Controllers\LinksScraper;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SettingsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group(['middleware' => 'loginChecker'], function () {
    Route::get('/', function () {
        return view('home');
    })->name('home');

    Route::match(['get', 'post'], '/training', [UploadTrainingData::class, 'index'])->name('training');

    Route::match(['get', 'post'], '/settings', [SettingsController::class, 'index'])->name('settings');


    Route::get('/scrape', [LinksScraper::class, 'index'])->name('scrape');


    Route::get('/script-tag', function () {
        return view('script');
    })->name('chatbot.script');

    Route::get('/test', function () {
        return view('test');
    })->name('test');
    
});

Route::match(['get', 'post'], '/login', [LoginController::class, 'index'])->name('login');

Route::get('/chatbot-widget', function () {
    return view('fullscreen-chat');
})->name('fullscreen-chat');
