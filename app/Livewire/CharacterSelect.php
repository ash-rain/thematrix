<?php

namespace App\Livewire;

use App\Ai\Agents\MatrixGameAgent;
use App\Enums\Character;
use Laravel\Ai\Exceptions\AiException;
use Laravel\Ai\Exceptions\RateLimitedException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.game')]
#[Title('Choose Your Path — The Matrix: Terminal')]
class CharacterSelect extends Component
{
    public ?string $selectedCharacter = null;

    public bool $isStarting = false;

    public ?string $errorMessage = null;

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

        try {
            $response = $agent
                ->forUser($sessionUser)
                ->prompt("Begin the adventure. Set the opening scene for {$character->displayName()}. Establish the atmosphere and present the first three choices.");
        } catch (RateLimitedException) {
            $this->errorMessage = 'The system is overloaded. Too many operatives in the Matrix. Try again in a moment.';
            $this->isStarting = false;

            return;
        } catch (AiException) {
            $this->errorMessage = 'A glitch in the Matrix. The connection was lost. Try again.';
            $this->isStarting = false;

            return;
        }

        session([
            'game_character' => $character->value,
            'game_health' => $response['health'],
            'game_inventory' => $response['inventory'],
            'game_conversation_id' => $response->conversationId,
            'game_turn_count' => 1,
            'game_status' => 'active',
        ]);

        $this->redirect(route('game.play'), navigate: true);
    }

    public function render()
    {
        return view('livewire.character-select', [
            'characters' => Character::cases(),
        ]);
    }
}
