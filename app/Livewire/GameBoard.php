<?php

namespace App\Livewire;

use App\Ai\Agents\MatrixGameAgent;
use App\Enums\Character;
use Laravel\Ai\Image;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.game')]
class GameBoard extends Component
{
    public string $characterName = '';

    public int $turnCount = 0;

    public string $narrative = '';

    public int $health = 100;

    public array $inventory = [];

    public array $choices = [];

    public ?string $sceneImage = null;

    public bool $isLoading = false;

    public bool $gameOver = false;

    public function mount(): void
    {
        if (! session()->has('game_character')) {
            $this->redirect(route('game.select'), navigate: true);

            return;
        }

        $character = Character::from(session('game_character'));

        $this->characterName = $character->displayName();
        $this->turnCount = session('game_turn_count', 0);
        $this->health = session('game_health', 100);
        $this->inventory = session('game_inventory', []);
        $this->sceneImage = session('game_scene_image_path');
        $this->gameOver = session('game_status') === 'game_over' || $this->health <= 0;

        if ($this->turnCount === 1 && session()->has('game_conversation_id')) {
            $this->loadLatestTurn();
        }
    }

    public function getTitle(): string
    {
        return $this->characterName.' — The Matrix: Terminal';
    }

    public function makeChoice(int $choiceIndex): void
    {
        if ($this->isLoading || $this->gameOver || ! isset($this->choices[$choiceIndex])) {
            return;
        }

        $this->isLoading = true;
        $choiceText = $this->choices[$choiceIndex];

        $character = Character::from(session('game_character'));
        $sessionUser = (object) ['id' => session()->getId()];

        $agent = new MatrixGameAgent(
            character: $character,
            health: $this->health,
            inventory: $this->inventory,
            turnCount: $this->turnCount,
        );

        $response = $agent
            ->continue(session('game_conversation_id'), as: $sessionUser)
            ->prompt("The player chose: \"{$choiceText}\"");

        $this->narrative = $response['narrative'];
        $this->health = max(0, min(100, $response['health']));
        $this->inventory = $response['inventory'];
        $this->choices = $response['choices'];
        $this->gameOver = $response['game_over'] || $this->health <= 0;
        $this->turnCount++;

        $newStatus = $this->gameOver ? 'game_over' : 'active';

        session([
            'game_health' => $this->health,
            'game_inventory' => $this->inventory,
            'game_turn_count' => $this->turnCount,
            'game_status' => $newStatus,
        ]);

        Image::of($response['scene_description'].' Digital art, cinematic, The Matrix movie style, green tint, dark cyberpunk noir atmosphere.')
            ->landscape()
            ->queue()
            ->then(function ($image) {
                $path = $image->storePublicly();
                session(['game_scene_image_path' => $path]);
            });

        $this->isLoading = false;
    }

    public function refreshImage(): void
    {
        $this->sceneImage = session('game_scene_image_path');
    }

    public function newGame(): void
    {
        session()->forget([
            'game_character',
            'game_health',
            'game_inventory',
            'game_conversation_id',
            'game_turn_count',
            'game_status',
            'game_scene_image_path',
        ]);

        $this->redirect(route('game.select'), navigate: true);
    }

    protected function loadLatestTurn(): void
    {
        $character = Character::from(session('game_character'));
        $sessionUser = (object) ['id' => session()->getId()];

        $agent = new MatrixGameAgent(
            character: $character,
            health: $this->health,
            inventory: $this->inventory,
            turnCount: $this->turnCount,
        );

        $response = $agent
            ->continue(session('game_conversation_id'), as: $sessionUser)
            ->prompt('Recap the current scene briefly and present the three choices again.');

        $this->narrative = $response['narrative'];
        $this->health = $response['health'];
        $this->inventory = $response['inventory'];
        $this->choices = $response['choices'];
    }

    public function render()
    {
        return view('livewire.game-board')
            ->title($this->getTitle());
    }
}
