<?php

use App\Ai\Agents\MatrixGameAgent;
use App\Enums\Character;
use Laravel\Ai\Exceptions\RateLimitedException;

test('user with game session can view game board', function () {
    $this->withSession([
        'game_character' => 'neo',
        'game_health' => 100,
        'game_inventory' => ['Phone'],
        'game_conversation_id' => 'test-conv-id',
        'game_turn_count' => 2,
        'game_status' => 'active',
    ])->get(route('game.play'))
        ->assertOk()
        ->assertSee('THE MATRIX: TERMINAL');
});

test('user without game session is redirected to character select', function () {
    $this->get(route('game.play'))
        ->assertRedirect(route('game.select'));
});

test('making a choice updates session state', function () {
    MatrixGameAgent::fake();

    session([
        'game_character' => 'trinity',
        'game_health' => 100,
        'game_inventory' => Character::Trinity->startingInventory(),
        'game_conversation_id' => 'test-conv-id',
        'game_turn_count' => 1,
        'game_status' => 'active',
    ]);

    Livewire\Livewire::test(\App\Livewire\GameBoard::class)
        ->set('choices', ['Fight the agent', 'Run away', 'Hack the system'])
        ->set('narrative', 'You stand in the hallway...')
        ->call('makeChoice', 0);

    expect(session('game_turn_count'))->toBe(2);

    MatrixGameAgent::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 'Fight the agent'));
});

test('game over when health reaches zero', function () {
    session([
        'game_character' => 'neo',
        'game_health' => 0,
        'game_inventory' => [],
        'game_conversation_id' => 'test-conv-id',
        'game_turn_count' => 5,
        'game_status' => 'game_over',
    ]);

    Livewire\Livewire::test(\App\Livewire\GameBoard::class)
        ->assertSet('gameOver', true);
});

test('rate limit error during make choice sets error message', function () {
    MatrixGameAgent::fake(fn () => throw RateLimitedException::forProvider('ollama'));

    session([
        'game_character' => 'trinity',
        'game_health' => 100,
        'game_inventory' => Character::Trinity->startingInventory(),
        'game_conversation_id' => 'test-conv-id',
        'game_turn_count' => 2,
        'game_status' => 'active',
    ]);

    Livewire\Livewire::test(\App\Livewire\GameBoard::class)
        ->set('choices', ['Fight the agent', 'Run away', 'Hack the system'])
        ->set('narrative', 'You stand in the hallway...')
        ->call('makeChoice', 0)
        ->assertSet('errorMessage', 'The system is overloaded. Too many operatives in the Matrix. Try again in a moment.')
        ->assertSet('isLoading', false);

    expect(session('game_turn_count'))->toBe(2);
});
