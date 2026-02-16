<?php

use App\Ai\Agents\MatrixGameAgent;
use App\Enums\Character;
use App\Enums\GameStatus;
use App\Jobs\GenerateSceneImage;
use App\Livewire\CharacterSelect;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Exceptions\RateLimitedException;

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
    Queue::fake(GenerateSceneImage::class);

    Livewire\Livewire::test(CharacterSelect::class)
        ->call('selectCharacter', 'neo')
        ->assertSet('selectedCharacter', 'neo')
        ->call('startGame')
        ->assertRedirect(route('game.play'));

    expect(session('game_character'))->toBe('neo')
        ->and(session('game_health'))->toBeInt()
        ->and(session('game_status'))->toBe(GameStatus::Active->value)
        ->and(session('game_turn_count'))->toBe(1);

    MatrixGameAgent::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 'Neo'));

    Queue::assertPushed(GenerateSceneImage::class);
});

test('user cannot start a game without selecting a character', function () {
    Livewire\Livewire::test(CharacterSelect::class)
        ->call('startGame');

    expect(session()->has('game_character'))->toBeFalse();
});

test('rate limit error during game start sets error message', function () {
    MatrixGameAgent::fake(fn () => throw RateLimitedException::forProvider('ollama'));

    Livewire\Livewire::test(CharacterSelect::class)
        ->call('selectCharacter', 'neo')
        ->call('startGame')
        ->assertSet('errorMessage', CharacterSelect::RATE_LIMIT_ERROR)
        ->assertSet('isStarting', false)
        ->assertNoRedirect();

    expect(session()->has('game_character'))->toBeFalse();
});
