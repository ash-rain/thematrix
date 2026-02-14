<?php

use App\Ai\Agents\MatrixGameAgent;
use App\Enums\Character;
use Laravel\Ai\Image;

test('user with game session can view game board', function () {
    $this->withSession([
        'game_character' => 'neo',
        'game_health' => 100,
        'game_inventory' => ['Phone'],
        'game_conversation_id' => 'test-conv-id',
        'game_turn_count' => 2,
        'game_status' => 'active',
        'game_scene_image_path' => null,
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
    Image::fake();

    session([
        'game_character' => 'trinity',
        'game_health' => 100,
        'game_inventory' => Character::Trinity->startingInventory(),
        'game_conversation_id' => 'test-conv-id',
        'game_turn_count' => 1,
        'game_status' => 'active',
        'game_scene_image_path' => null,
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
        'game_scene_image_path' => null,
    ]);

    Livewire\Livewire::test(\App\Livewire\GameBoard::class)
        ->assertSet('gameOver', true);
});
