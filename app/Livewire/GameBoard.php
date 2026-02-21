<?php

namespace App\Livewire;

use App\Ai\Agents\MatrixGameAgent;
use App\Enums\Character;
use App\Jobs\GenerateSceneImage;
use Illuminate\Support\Facades\Cache;
use Laravel\Ai\Exceptions\AiException;
use Laravel\Ai\Exceptions\RateLimitedException;
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

    public bool $isLoading = false;

    public bool $gameOver = false;

    public ?string $sceneImage = null;

    public ?string $errorMessage = null;

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
        $this->gameOver = session('game_status') === 'game_over' || $this->health <= 0;

        $this->sceneImage = Cache::get('game_scene_image:'.session()->getId());

        if ($this->turnCount === 1 && session()->has('game_conversation_id')) {
            $this->loadLatestTurn();
        }
    }

    public function refreshImage(): void
    {
        $path = Cache::get('game_scene_image:'.session()->getId());

        if ($path) {
            $this->sceneImage = $path;
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

        try {
            $response = $agent
                ->continue(session('game_conversation_id'), as: $sessionUser)
                ->prompt("The player chose: \"{$choiceText}\"");
        } catch (RateLimitedException) {
            $this->errorMessage = 'The system is overloaded. Too many operatives in the Matrix. Try again in a moment.';
            $this->isLoading = false;

            return;
        } catch (AiException $e) {
            $this->errorMessage = 'A glitch in the Matrix. '.$e->getMessage();
            $this->isLoading = false;

            return;
        }

        $this->errorMessage = null;
        $this->narrative = $response['narrative'];
        $this->health = max(0, min(100, $response['health']));
        $this->inventory = $response['inventory'];
        $this->choices = $response['choices'];
        $this->gameOver = $response['game_over'] || $this->health <= 0;
        $this->turnCount++;

        $sessionId = session()->getId();
        Cache::forget("game_scene_image:{$sessionId}");
        $this->sceneImage = null;

        // Queue image generation with OpenRouter
        GenerateSceneImage::dispatch(
            $response['scene_description'].' Digital art, cinematic, The Matrix movie style, green tint, dark cyberpunk noir atmosphere.',
            $sessionId,
            'bytedance-seed/seedream-4.5'
        );

        $newStatus = $this->gameOver ? 'game_over' : 'active';

        session([
            'game_health' => $this->health,
            'game_inventory' => $this->inventory,
            'game_turn_count' => $this->turnCount,
            'game_status' => $newStatus,
        ]);

        $this->isLoading = false;
    }

    public function newGame(): void
    {
        Cache::forget('game_scene_image:'.session()->getId());

        session()->forget([
            'game_character',
            'game_health',
            'game_inventory',
            'game_conversation_id',
            'game_turn_count',
            'game_status',
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

        try {
            $response = $agent
                ->continue(session('game_conversation_id'), as: $sessionUser)
                ->prompt('Recap the current scene briefly and present the three choices again.');
        } catch (RateLimitedException) {
            $this->errorMessage = 'The system is overloaded. Too many operatives in the Matrix. Try again in a moment.';

            return;
        } catch (AiException $e) {
            $this->errorMessage = 'A glitch in the Matrix. '.$e->getMessage();

            return;
        }

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
