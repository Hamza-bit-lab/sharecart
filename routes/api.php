<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ListController;
use App\Http\Controllers\Api\ListItemController;
use App\Http\Controllers\SuggestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;

// Public auth APIs
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Legal and Info endpoints
Route::get('/privacy-policy', [\App\Http\Controllers\Api\PageController::class, 'privacy']);
Route::get('/terms-and-conditions', [\App\Http\Controllers\Api\PageController::class, 'terms']);
Route::get('/faqs', [\App\Http\Controllers\Api\PageController::class, 'faq']);

// Join list by code (no login): returns list + guest access_token for that list
Route::post('/lists/join-code', [ListController::class, 'joinByCode']);

// Protected APIs (token via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Lists (require full auth)
    Route::get('/lists/icons', [ListController::class, 'icons']);
    Route::get('/lists', [ListController::class, 'index']);
    Route::post('/lists', [ListController::class, 'store']);
    Route::match(['put', 'patch'], '/lists/{list}', [ListController::class, 'update']);
    Route::delete('/lists/{list}', [ListController::class, 'destroy']);
    Route::post('/lists/{list}/archive', [ListController::class, 'archive']);
    Route::post('/lists/{list}/restore', [ListController::class, 'restore']);

    // Sharing (owner only)
    Route::post('/lists/{list}/share', [ListController::class, 'share']);
    Route::delete('/lists/{list}/share/{user}', [ListController::class, 'unshare']);

    // Suggestions
    Route::get('/suggestions', [SuggestionController::class, 'index']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Templates
    Route::name('api.')->group(function () {
        Route::apiResource('templates', \App\Http\Controllers\Api\TemplateController::class);
        Route::post('/templates/{template}/apply/{list}', [\App\Http\Controllers\Api\TemplateController::class, 'apply'])->name('templates.apply');
    });

    // FCM Tokens
    Route::post('/fcm-token', [\App\Http\Controllers\Api\FcmTokenController::class, 'store']);
});

// List view + items + reset: allow Sanctum user OR guest token (from join-code)
Route::middleware('list.access.api')->group(function () {
    Route::get('/lists/{list}', [ListController::class, 'show']);
    Route::get('/lists/{list}/predictive-suggestions', [\App\Http\Controllers\SuggestionController::class, 'predictiveSuggestions']);
    Route::get('/lists/{list}/settlement', [ListController::class, 'settlement']);
    Route::post('/lists/{list}/leave', [ListController::class, 'leave']);
    Route::post('/lists/{list}/ping', [ListController::class, 'ping']);
    Route::post('/lists/{list}/reset-items', [ListController::class, 'resetItems']);
    Route::post('/lists/{list}/items', [ListItemController::class, 'store']);
    Route::post('/lists/{list}/items/reorder', [ListItemController::class, 'reorder']);
    Route::match(['put', 'patch'], '/lists/{list}/items/{item}', [ListItemController::class, 'update']);
    Route::post('/lists/{list}/items/{item}/claim', [ListItemController::class, 'claim']);
    Route::post('/lists/{list}/items/{item}/unclaim', [ListItemController::class, 'unclaim']);
    Route::post('/lists/{list}/items/{item}/out-of-stock', [ListItemController::class, 'toggleOutOfStock']);
    Route::delete('/lists/{list}/items/{item}', [ListItemController::class, 'destroy']);

    // Item Chat
    Route::get('/lists/{list}/items/{item}/messages', [\App\Http\Controllers\Api\ItemMessageController::class, 'index']);
    Route::post('/lists/{list}/items/{item}/messages', [\App\Http\Controllers\Api\ItemMessageController::class, 'store']);

    // Payments
    Route::get('/lists/{list}/payments', [\App\Http\Controllers\Api\ListPaymentController::class, 'index']);
    Route::post('/lists/{list}/payments', [\App\Http\Controllers\Api\ListPaymentController::class, 'store']);
    Route::match(['put', 'patch'], '/lists/{list}/payments/{payment}', [\App\Http\Controllers\Api\ListPaymentController::class, 'update']);
    Route::delete('/lists/{list}/payments/{payment}', [\App\Http\Controllers\Api\ListPaymentController::class, 'destroy']);
});



//book app
Route::get('/book-version', [BookController::class, 'getVersion']);
Route::post('/book-update-version', [BookController::class, 'updateVersion']);