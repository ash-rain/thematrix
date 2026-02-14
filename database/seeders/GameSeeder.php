<?php

namespace Database\Seeders;

use App\Enums\Character;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create([
            'name' => 'Test Player',
            'email' => 'player@matrix.test',
        ]);

        foreach (Character::cases() as $character) {
            Game::factory()
                ->for($user)
                ->withCharacter($character)
                ->create(['turn_count' => fake()->numberBetween(1, 20)]);
        }
    }
}
