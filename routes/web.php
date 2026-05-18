<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\RecipientController;
use Illuminate\Support\Facades\Route;

// Google OAuth
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::post('/auth/logout', [GoogleAuthController::class, 'logout'])->name('auth.logout');

// API — returns current session user as JSON (used by the Vue SPA)
Route::get('/api/user', [AuthController::class, 'user'])->name('api.user');

Route::middleware('auth')->group(function () {
    Route::get('/api/users', [\App\Http\Controllers\Api\UserController::class, 'index']);
    Route::post('/api/users', [\App\Http\Controllers\Api\UserController::class, 'store']);
    Route::put('/api/users/{user}', [\App\Http\Controllers\Api\UserController::class, 'update']);
    Route::delete('/api/users/{user}', [\App\Http\Controllers\Api\UserController::class, 'destroy']);

    Route::get('/api/documents', [DocumentController::class, 'index']);
    Route::post('/api/documents', [DocumentController::class, 'store']);
    Route::get('/api/documents/{document}', [DocumentController::class, 'show']);
    Route::put('/api/documents/{document}', [DocumentController::class, 'update']);
    Route::delete('/api/documents/{document}', [DocumentController::class, 'destroy']);
    Route::post('/api/documents/{document}/fields', [DocumentController::class, 'saveFields']);

    Route::get('/api/recipients', [RecipientController::class, 'index']);
    Route::post('/api/recipients', [RecipientController::class, 'store']);
    Route::put('/api/recipients/{recipient}', [RecipientController::class, 'update']);
    Route::delete('/api/recipients/{recipient}', [RecipientController::class, 'destroy']);
    Route::post('/api/recipients/{recipient}/mark-sent', [RecipientController::class, 'markAsSent']);

    Route::get('/api/invitation-links', [\App\Http\Controllers\Api\InvitationLinkController::class, 'index']);
    Route::post('/api/invitation-links', [\App\Http\Controllers\Api\InvitationLinkController::class, 'store']);
    Route::delete('/api/invitation-links/{link}', [\App\Http\Controllers\Api\InvitationLinkController::class, 'destroy']);
});

Route::get('/api/invitation-links/{token}', [\App\Http\Controllers\Api\InvitationLinkController::class, 'show']);
Route::get('/api/public/documents/{document}', [DocumentController::class, 'publicShow']);

// Serve storage files without symlink (required on Hostinger shared hosting
// where symlink() and exec() are disabled).
Route::get('/file/{path}', function (string $path) {
    $path = ltrim($path, '/');

    if (str_contains($path, '..')) {
        abort(404);
    }

    $fullPath = storage_path('app/public/' . $path);

    if (!file_exists($fullPath)) {
        abort(404);
    }

    return response()->file($fullPath);
})->where('path', '.*');

// Catch-all: serve the Vue SPA
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*')->name('login');
