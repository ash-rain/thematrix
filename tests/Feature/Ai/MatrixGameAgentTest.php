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

    expect($response['narrative'])->toBeString()
        ->and($response['scene_description'])->toBeString()
        ->and($response['health'])->toBeInt()
        ->and($response['inventory'])->toBeArray()
        ->and($response['choices'])->toBeArray()
        ->and($response['game_over'])->toBeBool();

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

    expect($instructions)
        ->toContain('Morpheus')
        ->toContain('The Guide')
        ->toContain('Health: 75/100')
        ->toContain('red pill, blue pill')
        ->toContain('Turn: 5');
});
