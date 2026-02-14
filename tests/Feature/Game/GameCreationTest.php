<?php

use App\Ai\Agents\MatrixGameAgent;
use App\Enums\Character;
use Laravel\Ai\Image;

test('guests can view character select page', function () {
    $this->get(route('game.select'))
        ->assertOk()
        ->assertSee('THE MATRIX')
        ->assertSee('CHOOSE YOUR PATH');
});

test('character select page displays all five characters', function () {
    $response = $this->get(route('game.select'));

    foreach (Character::cases() as $character) {
        $response->assertSee(strtoupper($character->displayName()));
    }
});

test('user can start a game with a valid character', function () {
    MatrixGameAgent::fake();
    Image::fake();

    Livewire\Livewire::test(\App\Livewire\CharacterSelect::class)
        ->call('selectCharacter', 'neo')
        ->assertSet('selectedCharacter', 'neo')
        ->call('startGame')
        ->assertRedirect(route('game.play'));

    expect(session('game_character'))->toBe('neo');
    expect(session('game_health'))->toBeInt();
    expect(session('game_status'))->toBe('active');
    expect(session('game_turn_count'))->toBe(1);

    MatrixGameAgent::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 'Neo'));
});

test('user cannot start a game without selecting a character', function () {
    Livewire\Livewire::test(\App\Livewire\CharacterSelect::class)
        ->call('startGame');

    expect(session()->has('game_character'))->toBeFalse();
});
