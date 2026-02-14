<?php

namespace App\Livewire;

use App\Ai\Agents\MatrixGameAgent;
use App\Enums\Character;
use Laravel\Ai\Image;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.game')]
#[Title('Choose Your Path — The Matrix: Terminal')]
class CharacterSelect extends Component
{
    public ?string $selectedCharacter = null;

    public bool $isStarting = false;

    public function selectCharacter(string $character): void
    {
        $this->selectedCharacter = $character;
    }

    public function startGame(): void
    {
        if (! $this->selectedCharacter) {
            return;
        }

        $this->isStarting = true;

        $character = Character::from($this->selectedCharacter);
        $sessionUser = (object) ['id' => session()->getId()];

        $agent = new MatrixGameAgent(
            character: $character,
            health: 100,
            inventory: $character->startingInventory(),
            turnCount: 0,
        );

        $response = $agent
            ->forUser($sessionUser)
            ->prompt("Begin the adventure. Set the opening scene for {$character->displayName()}. Establish the atmosphere and present the first three choices.");

        session([
            'game_character' => $character->value,
            'game_health' => $response['health'],
            'game_inventory' => $response['inventory'],
            'game_conversation_id' => $response->conversationId,
            'game_turn_count' => 1,
            'game_status' => 'active',
            'game_scene_image_path' => null,
        ]);

        Image::of($response['scene_description'].' Digital art, cinematic, The Matrix movie style, green tint, dark cyberpunk noir atmosphere.')
            ->landscape()
            ->queue()
            ->then(function ($image) {
                $path = $image->storePublicly();
                session(['game_scene_image_path' => $path]);
            });

        $this->redirect(route('game.play'), navigate: true);
    }

    public function render()
    {
        return view('livewire.character-select', [
            'characters' => Character::cases(),
        ]);
    }
}
