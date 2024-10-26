<?php

use App\Http\Controllers\GoogleCalendarController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    if(is_null(session('google_access_token'))) return redirect()->route('google-auth');
    return view('meet');
});

# Authenticate using a Gmail account to obtain a Google access token, which grants the permission to generate a meeting.
Route::get('/auth/google', [GoogleCalendarController::class, 'redirectToGoogle'])->name('google-auth');

# Define a callback route that will be called by Google after successful authentication.
Route::get('/callback', [GoogleCalendarController::class, 'handleGoogleCallback']);

# Request to generate a meeting
Route::post('/generate-meet-link', [GoogleCalendarController::class, 'createGoogleCalendarEvent']);