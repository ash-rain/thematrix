<?php

namespace App\Enums;

enum Character: string
{
    case Neo = 'neo';
    case Trinity = 'trinity';
    case Morpheus = 'morpheus';
    case Niobe = 'niobe';
    case Ghost = 'ghost';

    public function displayName(): string
    {
        return match ($this) {
            self::Neo => 'Neo',
            self::Trinity => 'Trinity',
            self::Morpheus => 'Morpheus',
            self::Niobe => 'Niobe',
            self::Ghost => 'Ghost',
        };
    }

    public function tagline(): string
    {
        return match ($this) {
            self::Neo => 'The One',
            self::Trinity => 'The Warrior',
            self::Morpheus => 'The Guide',
            self::Niobe => 'The Captain',
            self::Ghost => 'The Assassin',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Neo => 'A hacker who discovers he is The One, destined to end the war between humans and machines. Excels at bending the rules of the Matrix.',
            self::Trinity => 'A fearless operative and expert martial artist. Known for impossible feats and unwavering loyalty to the cause.',
            self::Morpheus => 'Captain of the Nebuchadnezzar and a legendary freedom fighter. A master strategist who believes in prophecy.',
            self::Niobe => 'Captain of the Logos and the fastest hovercraft pilot in the fleet. Pragmatic, resourceful, and deadly in combat.',
            self::Ghost => 'A silent and lethal operative aboard the Logos. A philosopher-assassin who fights with precision and purpose.',
        };
    }

    public function startingInventory(): array
    {
        return match ($this) {
            self::Neo => ['worn trench coat', 'cell phone'],
            self::Trinity => ['twin pistols', 'motorcycle key'],
            self::Morpheus => ['red pill', 'blue pill'],
            self::Niobe => ['hovercraft access card', 'combat knife'],
            self::Ghost => ['silenced pistol', 'meditation beads'],
        };
    }
}
