<?php

use App\Livewire\CharacterSelect;
use App\Livewire\GameBoard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/play', CharacterSelect::class)->name('game.select');
Route::get('/play/game', GameBoard::class)->name('game.play');

require __DIR__.'/settings.php';
