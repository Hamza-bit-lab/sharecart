<?php

use App\Http\Controllers\GroceryListController;
use App\Http\Controllers\ListItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ListPollController;
use App\Http\Controllers\SuggestionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return redirect()->route('lists.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// Invite link: anyone with the link can join the list (after login if needed)
Route::get('/lists/join/{token}', [GroceryListController::class, 'joinByInvite'])->name('lists.join');

// Join list by code (public: no login required)
Route::get('/join', [GroceryListController::class, 'joinByCodeForm'])->name('join');
Route::post('/join', [GroceryListController::class, 'joinByCode'])->name('join.submit');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Grocery lists (auth-only: index, create, store, edit, update, destroy, share, archive, restore)
    Route::get('/lists', [GroceryListController::class, 'index'])->name('lists.index');
    Route::get('/lists/create', [GroceryListController::class, 'create'])->name('lists.create');
    Route::post('/lists', [GroceryListController::class, 'store'])->name('lists.store');
    Route::get('/lists/{list}/edit', [GroceryListController::class, 'edit'])->name('lists.edit');
    Route::match(['put', 'patch'], '/lists/{list}', [GroceryListController::class, 'update'])->name('lists.update');
    Route::delete('/lists/{list}', [GroceryListController::class, 'destroy'])->name('lists.destroy');
    Route::post('/lists/{list}/share', [GroceryListController::class, 'share'])->name('lists.share');
    Route::delete('/lists/{list}/share/{user}', [GroceryListController::class, 'unshare'])->name('lists.unshare');
    Route::post('/lists/{list}/archive', [GroceryListController::class, 'archive'])->name('lists.archive');
    Route::post('/lists/{list}/restore', [GroceryListController::class, 'restore'])->name('lists.restore');

    // Suggestions (common items from all lists)
    Route::get('/suggestions', [SuggestionController::class, 'index'])->name('suggestions.index');
});

// List view + items + poll: allowed for auth (policy) or guest (joined by code in session)
Route::middleware('list.access')->group(function () {
    Route::get('/lists/{list}', [GroceryListController::class, 'show'])->name('lists.show');
    Route::post('/lists/{list}/guest-name', [GroceryListController::class, 'setGuestName'])->name('lists.guest-name');
    Route::post('/lists/{list}/reset-items', [GroceryListController::class, 'resetItems'])->name('lists.reset-items');
    Route::get('/lists/{list}/poll', [ListPollController::class, 'show'])->name('lists.poll');
    Route::post('/lists/{list}/items', [ListItemController::class, 'store'])->name('lists.items.store');
    Route::put('/lists/{list}/items/{item}', [ListItemController::class, 'update'])->name('lists.items.update');
    Route::delete('/lists/{list}/items/{item}', [ListItemController::class, 'destroy'])->name('lists.items.destroy');
});

require __DIR__.'/auth.php';
