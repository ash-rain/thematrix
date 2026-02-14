<?php

use App\Ai\Agents\MatrixGameAgent;
use App\Enums\Character;
use App\Models\User;

test('agent returns structured output when faked', function () {
    MatrixGameAgent::fake();

    $user = User::factory()->create();

    $agent = new MatrixGameAgent(
        character: Character::Neo,
        health: 100,
        inventory: ['worn trench coat', 'cell phone'],
        turnCount: 0,
    );

    $response = $agent
        ->forUser($user)
        ->prompt('Begin the adventure.');

    expect($response['narrative'])->toBeString();
    expect($response['health'])->toBeInt();
    expect($response['inventory'])->toBeArray();
    expect($response['choices'])->toBeArray();
    expect($response['game_over'])->toBeBool();

    MatrixGameAgent::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 'Begin the adventure'));
});

test('agent includes character info in instructions', function () {
    $agent = new MatrixGameAgent(
        character: Character::Morpheus,
        health: 75,
        inventory: ['red pill', 'blue pill'],
        turnCount: 5,
    );

    $instructions = $agent->instructions();

    expect($instructions)->toContain('Morpheus');
    expect($instructions)->toContain('The Guide');
    expect($instructions)->toContain('Health: 75/100');
    expect($instructions)->toContain('red pill, blue pill');
    expect($instructions)->toContain('Turn: 5');
});
