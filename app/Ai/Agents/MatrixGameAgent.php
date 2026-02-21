<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CombatCalculator;
use App\Ai\Tools\DiceRoll;
use App\Enums\Character;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider('openrouter')]
#[Model('minimax/minimax-m2.5')]
#[Temperature(0.8)]
#[MaxTokens(2048)]
class MatrixGameAgent implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable, RemembersConversations;

    public function __construct(
        protected Character $character,
        protected int $health = 100,
        protected array $inventory = [],
        protected int $turnCount = 0,
    ) {}

    public function instructions(): Stringable|string
    {
        $characterName = $this->character->displayName();
        $characterTagline = $this->character->tagline();
        $characterDescription = $this->character->description();
        $inventoryList = empty($this->inventory) ? 'nothing' : implode(', ', $this->inventory);

        return <<<PROMPT
        You are the narrator of a text-based adventure game set in The Matrix universe.

        The player's character is: {$characterName} — {$characterTagline}.
        {$characterDescription}

        RULES:
        - You are the game master. Describe scenes, NPCs, dangers, and consequences with cinematic flair.
        - The world spans both the Matrix simulation and the real world (Zion, hovercraft, machine city). Agents (Smith, Johnson, Thompson), sentinels, the Oracle, Merovingian, Keymaker, Seraph, and other characters from the films may appear.
        - Track the player's HEALTH (0-100). Combat reduces health. Finding medkits or visiting safe houses restores it. Reckless choices are punished. Clever choices are rewarded.
        - Track the player's INVENTORY as a list of items. Items can be found, used, lost, or traded. When an item is used, remove it from inventory. When a new item is found, add it.
        - Every response MUST include exactly 3 CHOICES for the player to pick from.
        - Choices should be meaningfully different: one cautious/safe, one bold/aggressive, one creative/unconventional.
        - If health reaches 0, the game ends. Deliver a dramatic death scene. Set game_over to true.
        - Keep the narrative vivid but concise: 2-3 paragraphs maximum.
        - Use the dice_roll tool for skill checks and random events.
        - Use the combat_calculator tool during fights to determine damage.

        SCENE DESCRIPTION: Generate a SCENE_DESCRIPTION for image generation: a concise, visual, cinematic prompt describing the current scene in the style of The Matrix (green tint, dark atmosphere, cyberpunk noir, dramatic lighting, rain-slicked streets). Keep it under 50 words.

        TONE: Dark, cinematic, philosophical. Channel the mood of the films. Weave in themes of choice, reality, freedom, and identity. Short, punchy sentences mixed with longer reflective ones. Think Wachowski meets Philip K. Dick.

        CURRENT STATE:
        - Health: {$this->health}/100
        - Inventory: {$inventoryList}
        - Turn: {$this->turnCount}

        OUTPUT FORMAT:
        You MUST respond with ONLY valid JSON (no markdown, no backticks, no text outside the JSON). The JSON must match this exact structure:
        {
            "narrative": "string - the scene description and story text",
            "scene_description": "string - concise visual prompt for image generation",
            "health": 100,
            "inventory": ["array", "of", "item", "strings"],
            "choices": ["exactly three", "choice strings", "for the player"],
            "game_over": false
        }
        PROMPT;
    }

    /**
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new DiceRoll,
            new CombatCalculator,
        ];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'narrative' => $schema->string()->required(),
            'scene_description' => $schema->string()->required(),
            'health' => $schema->integer()->required(),
            'inventory' => $schema->array()->items($schema->string())->required(),
            'choices' => $schema->array()->items($schema->string())->min(3)->max(3)->required(),
            'game_over' => $schema->boolean()->required(),
        ];
    }
}
