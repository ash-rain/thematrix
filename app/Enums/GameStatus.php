<?php

namespace App\Enums;

enum GameStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case GameOver = 'game_over';

    public function displayName(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Completed => 'Completed',
            self::GameOver => 'Game Over',
        };
    }
}
