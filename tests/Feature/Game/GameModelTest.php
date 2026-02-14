<?php

use App\Enums\Character;
use App\Enums\GameStatus;
use App\Models\Game;

test('inventory is cast to array', function () {
    $game = Game::factory()->withCharacter(Character::Neo)->make();

    expect($game->inventory)->toBeArray();
    expect($game->inventory)->toContain('worn trench coat');
});

test('character is cast to enum', function () {
    $game = Game::factory()->withCharacter(Character::Trinity)->make();

    expect($game->character)->toBeInstanceOf(Character::class);
    expect($game->character)->toBe(Character::Trinity);
});

test('status is cast to enum', function () {
    $game = Game::factory()->make();

    expect($game->status)->toBeInstanceOf(GameStatus::class);
    expect($game->status)->toBe(GameStatus::Active);
});

test('isActive returns true for active games', function () {
    $game = Game::factory()->make(['status' => GameStatus::Active]);

    expect($game->isActive())->toBeTrue();
});

test('isActive returns false for game over games', function () {
    $game = Game::factory()->gameOver()->make();

    expect($game->isActive())->toBeFalse();
});

test('isGameOver returns true when health is zero', function () {
    $game = Game::factory()->make(['health' => 0, 'status' => GameStatus::GameOver]);

    expect($game->isGameOver())->toBeTrue();
});

test('isGameOver returns false for active games with health', function () {
    $game = Game::factory()->make(['health' => 50, 'status' => GameStatus::Active]);

    expect($game->isGameOver())->toBeFalse();
});
