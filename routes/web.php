<?php

use App\Http\Controllers\AgendaItemController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\ShoppingItemController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('registrieren', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('registrieren', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('passwort-vergessen', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('passwort-vergessen', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    Route::get('passwort-zuruecksetzen/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('passwort-zuruecksetzen', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/events')->name('home');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profil/passwort', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::resource('events', EventController::class);

    Route::scopeBindings()->prefix('events/{event}')->name('events.')->group(function () {
        Route::get('ablauf', [AgendaItemController::class, 'index'])->name('agenda.index');
        Route::get('ablauf/neu', [AgendaItemController::class, 'create'])->name('agenda.create');
        Route::post('ablauf', [AgendaItemController::class, 'store'])->name('agenda.store');
        Route::get('ablauf/{agendaItem}/bearbeiten', [AgendaItemController::class, 'edit'])->name('agenda.edit');
        Route::put('ablauf/{agendaItem}', [AgendaItemController::class, 'update'])->name('agenda.update');
        Route::delete('ablauf/{agendaItem}', [AgendaItemController::class, 'destroy'])->name('agenda.destroy');

        Route::post('personen', [PersonController::class, 'store'])->name('people.store');

        Route::resource('rezepte', RecipeController::class)
            ->parameters(['rezepte' => 'recipe'])
            ->names('recipes');

        Route::get('einkauf', [ShoppingItemController::class, 'index'])->name('shopping.index');
        Route::get('einkauf/neu', [ShoppingItemController::class, 'create'])->name('shopping.create');
        Route::post('einkauf', [ShoppingItemController::class, 'store'])->name('shopping.store');
        Route::post('einkauf/generieren', [ShoppingItemController::class, 'generate'])->name('shopping.generate');
        Route::patch('einkauf/{shoppingItem}/erledigt', [ShoppingItemController::class, 'toggle'])->name('shopping.toggle');
        Route::delete('einkauf/{shoppingItem}', [ShoppingItemController::class, 'destroy'])->name('shopping.destroy');
    });
});
