<?php

namespace Database\Factories;

use App\Enums\Character;
use App\Enums\GameStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $character = fake()->randomElement(Character::cases());

        return [
            'user_id' => User::factory(),
            'character' => $character,
            'health' => 100,
            'inventory' => $character->startingInventory(),
            'conversation_id' => null,
            'status' => GameStatus::Active,
            'turn_count' => 0,
            'scene_image_path' => null,
        ];
    }

    public function gameOver(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GameStatus::GameOver,
            'health' => 0,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GameStatus::Completed,
        ]);
    }

    public function withCharacter(Character $character): static
    {
        return $this->state(fn (array $attributes) => [
            'character' => $character,
            'inventory' => $character->startingInventory(),
        ]);
    }
}
