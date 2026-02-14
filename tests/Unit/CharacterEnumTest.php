<?php

use App\Enums\Character;

test('all characters have display names', function () {
    foreach (Character::cases() as $character) {
        expect($character->displayName())->toBeString()->not->toBeEmpty();
    }
});

test('all characters have taglines', function () {
    foreach (Character::cases() as $character) {
        expect($character->tagline())->toBeString()->not->toBeEmpty();
    }
});

test('all characters have descriptions', function () {
    foreach (Character::cases() as $character) {
        expect($character->description())->toBeString()->not->toBeEmpty();
    }
});

test('all characters have starting inventories', function () {
    foreach (Character::cases() as $character) {
        expect($character->startingInventory())->toBeArray()->not->toBeEmpty();
    }
});

test('there are exactly five characters', function () {
    expect(Character::cases())->toHaveCount(5);
});
