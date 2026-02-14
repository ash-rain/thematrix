# The Matrix: Terminal — A Text-Based AI Game

> "Unfortunately, no one can be told what The Matrix is. You have to see it for yourself."

## Vision

A text-based adventure game set in The Matrix universe. Players choose a character — Neo, Trinity, Morpheus, Niobe, or Ghost — and navigate an AI-driven narrative through a stunningly crafted terminal interface. Every response from the AI agent includes a generated scene image, health tracking, inventory management, and exactly 3 choices to advance the story. The UI is a love letter to the green-on-black terminals from the film — monospace type, CRT glow, digital rain, the works.

---

## Architecture Overview

```
┌─────────────────────────────────────────────────┐
│                   Frontend                       │
│  Livewire 4 Components + Tailwind Terminal Theme │
│  ┌──────────┐ ┌──────────────────────────────┐  │
│  │ Character │ │         Game Board           │  │
│  │  Select   │ │ ┌────────┐ ┌─────────────┐  │  │
│  │  Screen   │ │ │ Scene  │ │  Narrative   │  │  │
│  └──────────┘ │ │ Image  │ │  + Choices   │  │  │
│               │ └────────┘ ├─────────────┤  │  │
│               │            │ Health + Inv │  │  │
│               │            └─────────────┘  │  │
│               └──────────────────────────────┘  │
├─────────────────────────────────────────────────┤
│                   Backend                        │
│  Laravel AI SDK Agent (Structured Output)        │
│  ┌──────────────────────────────────────┐       │
│  │ MatrixGameAgent                      │       │
│  │  - RemembersConversations            │       │
│  │  - HasStructuredOutput               │       │
│  │  - HasTools (DiceRoll, CombatCalc)   │       │
│  └──────────────────────────────────────┘       │
│  Image::of(scene_prompt)->generate()             │
├─────────────────────────────────────────────────┤
│              Admin (Filament 5)                   │
│  /admin — Game sessions, users, analytics        │
└─────────────────────────────────────────────────┘
```

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12.51 |
| Frontend reactivity | Livewire 4 |
| Styling | Tailwind CSS 4 (custom Matrix theme) |
| AI Agent | Laravel AI SDK (`laravel/ai`) |
| Image generation | Laravel AI SDK `Image::of()` (OpenAI/xAI) |
| Admin panel | Filament 5 |
| Database | SQLite (existing) |
| Auth | Laravel Fortify (existing) |
| Testing | Pest 4 |

---

## Phase 1: Foundation — Install Dependencies & Database

### 1.1 Install Laravel AI SDK

```bash
composer require laravel/ai
php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"
php artisan migrate
```

This creates `agent_conversations` and `agent_conversation_messages` tables automatically.

Add to `.env`:
```
OPENAI_API_KEY=sk-...
```

OpenAI will serve as the provider for both the game agent (text) and image generation.

### 1.2 Install Filament 5

```bash
composer require filament/filament:"^5.0"
php artisan filament:install --panels
```

This creates `app/Providers/Filament/AdminPanelProvider.php`. Register it in `bootstrap/providers.php`. Create an admin user with `php artisan make:filament-user`.

### 1.3 Database Schema

Create a `games` table to track game sessions:

```
games
├── id (primary key)
├── user_id (foreign key → users)
├── character (enum: neo, trinity, morpheus, niobe, ghost)
├── health (integer, default 100)
├── inventory (json, default [])
├── conversation_id (string, nullable — links to agent_conversations)
├── status (enum: active, completed, game_over)
├── turn_count (integer, default 0)
├── scene_image_path (string, nullable — latest scene image)
├── timestamps
```

Model: `App\Models\Game` with:
- `belongsTo(User::class)`
- `casts()`: inventory → array, character/status → enum
- Factory and seeder

Enums:
- `App\Enums\Character` — Neo, Trinity, Morpheus, Niobe, Ghost (with display names and descriptions)
- `App\Enums\GameStatus` — Active, Completed, GameOver

---

## Phase 2: The AI Agent

### 2.1 Create the MatrixGameAgent

```bash
php artisan make:agent MatrixGameAgent --structured
```

File: `app/Ai/Agents/MatrixGameAgent.php`

Implements:
- `Agent` — base agent contract
- `Conversational` + `RemembersConversations` — persistent memory across turns
- `HasStructuredOutput` — ensures every response has the exact structure we need
- `HasTools` — dice rolls and combat calculations

### 2.2 Agent Instructions (System Prompt)

The `instructions()` method returns a carefully crafted prompt:

```
You are the narrator of a text-based adventure game set in The Matrix universe.

The player's character is: {character_name} ({character_description}).

RULES:
- You are the game master. You describe scenes, NPCs, dangers, and consequences.
- The world is The Matrix (and the real world). Agents, sentinels, the Oracle,
  Merovingian, Keymaker, and other characters from the films may appear.
- Track the player's HEALTH (0-100). Actions have consequences — combat reduces
  health, finding medkits restores it, reckless choices are punished.
- Track the player's INVENTORY as a list of items. Items can be found, used, or lost.
- Every response MUST end with exactly 3 CHOICES for the player.
- Choices should be meaningfully different: one cautious, one bold, one creative.
- If health reaches 0, the game ends with a dramatic death scene.
- Keep responses vivid but concise (2-3 paragraphs max for the narrative).
- Generate a SCENE DESCRIPTION for image generation: a short, visual, cinematic
  prompt describing the current scene in the style of The Matrix film
  (green tint, dark, cyberpunk, noir).

TONE: Dark, cinematic, philosophical. Channel the mood of the films.
Weave in themes of choice, reality, freedom, and identity.

CURRENT STATE:
- Health: {health}
- Inventory: {inventory}
- Turn: {turn_count}
```

### 2.3 Structured Output Schema

```php
public function schema(JsonSchema $schema): array
{
    return [
        'narrative' => $schema->string()->required(),
        'health' => $schema->integer()->min(0)->max(100)->required(),
        'inventory' => $schema->array()->required(),
        'choices' => $schema->array()->minItems(3)->maxItems(3)->required(),
        'scene_description' => $schema->string()->required(),
        'game_over' => $schema->boolean()->required(),
    ];
}
```

Each `choices` item is a short string like "Take the red pill", "Fight the agent", "Hack the mainframe".

### 2.4 Agent Tools

Create two tools with `php artisan make:tool`:

**DiceRoll** — `app/Ai/Tools/DiceRoll.php`
- Rolls dice for combat/skill checks
- Schema: `sides` (integer), `count` (integer)
- Returns the roll result

**CombatCalculator** — `app/Ai/Tools/CombatCalculator.php`
- Calculates damage based on character stats and enemy type
- Schema: `attacker` (string), `defender` (string), `weapon` (string, optional)
- Returns damage dealt and taken

### 2.5 Image Generation

After each agent response, generate a scene image:

```php
$image = Image::of($response['scene_description'])
    ->landscape()
    ->generate();

$path = $image->storePublicly();
```

Queue image generation so the narrative appears instantly and the image loads when ready:

```php
Image::of($response['scene_description'])
    ->landscape()
    ->queue()
    ->then(function (ImageResponse $image) use ($game) {
        $path = $image->storePublicly();
        $game->update(['scene_image_path' => $path]);
    });
```

---

## Phase 3: Livewire Components & Game Logic

### 3.1 Character Select Component

**File:** `app/Livewire/CharacterSelect.php`
**View:** `resources/views/livewire/character-select.blade.php`

- Displays 5 character cards: Neo, Trinity, Morpheus, Niobe, Ghost
- Each card has: character portrait silhouette (CSS art), name, tagline, brief description
- Clicking a card selects the character with a glowing green border animation
- "Enter The Matrix" button creates a `Game` record, initiates the first agent conversation turn, and redirects to the game board
- The first turn prompt is: "Begin the adventure. Set the opening scene for {character_name}."

### 3.2 Game Board Component

**File:** `app/Livewire/GameBoard.php`
**View:** `resources/views/livewire/game-board.blade.php`

Properties:
- `Game $game` — the current game model
- `string $narrative` — current scene narrative text
- `int $health` — current health
- `array $inventory` — current inventory
- `array $choices` — current 3 choices
- `?string $sceneImage` — current scene image path
- `bool $isLoading` — loading state while agent responds
- `bool $gameOver` — game over flag

Methods:
- `mount(Game $game)` — load latest game state
- `makeChoice(int $choiceIndex)` — sends the chosen option to the agent, processes response, updates game state
- `newGame()` — redirects to character select

Flow of `makeChoice()`:
1. Set `$isLoading = true`
2. Continue the agent conversation: `(new MatrixGameAgent)->continue($game->conversation_id, as: $user)->prompt($choiceText)`
3. Parse structured response → update `$narrative`, `$health`, `$inventory`, `$choices`
4. Update `Game` model with new health, inventory, turn_count
5. Dispatch queued image generation
6. If `$response['game_over']` or health <= 0, set `$gameOver = true` and update game status
7. Set `$isLoading = false`

### 3.3 Routes

```php
// routes/web.php (add to existing)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/play', CharacterSelect::class)->name('game.select');
    Route::get('/play/{game}', GameBoard::class)->name('game.play');
});
```

---

## Phase 4: The Matrix Terminal UI

The entire game interface is themed as a terminal from The Matrix. This is the soul of the product.

### 4.1 Design System

**Colors:**
- Background: `#0a0a0a` (near-black)
- Primary text: `#00ff41` (Matrix green)
- Secondary text: `#008f11` (dim green)
- Accent: `#00ff41` with glow
- Danger (low health): `#ff0040`
- UI borders: `#003b00` (dark green)

**Typography:**
- Primary font: `'Fira Code', 'Courier New', monospace`
- All text is monospace — this is a terminal

**Effects:**
- CRT scanlines overlay (CSS `repeating-linear-gradient`)
- Subtle screen flicker animation
- Text typing animation (CSS `@keyframes` with `steps()`)
- Green glow on interactive elements (`text-shadow` / `box-shadow`)
- Matrix digital rain background (lightweight CSS animation with `@keyframes`)

### 4.2 Layout: Game Board

```
┌─────────────────────────────────────────────────────┐
│  ░░ THE MATRIX: TERMINAL ░░    ❤ Health: ████░░ 73  │  ← Top bar
├──────────────────────────┬──────────────────────────┤
│                          │                          │
│    [Generated Scene      │  > NARRATIVE TEXT        │
│     Image]               │  > displayed with        │
│                          │  > typing animation      │
│    640x360               │  > green monospace        │
│                          │  > on black               │
│                          │                          │
├──────────────────────────┤  ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ │
│  INVENTORY               │                          │
│  ┌──────┐ ┌──────┐      │  [1] > Take the red pill │  ← Choice buttons
│  │ 🔑   │ │ 💊   │      │  [2] > Fight the agent   │
│  │ Key  │ │ Pill │      │  [3] > Hack the mainframe│
│  └──────┘ └──────┘      │                          │
│                          │                          │
└──────────────────────────┴──────────────────────────┘
```

On mobile, this stacks vertically: image → narrative → choices → inventory + health.

### 4.3 Character Select Screen

```
┌─────────────────────────────────────────────────────┐
│                                                     │
│        ░▒▓ CHOOSE YOUR PATH ▓▒░                    │
│                                                     │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐              │
│  │  ╔═══╗  │ │  ╔═══╗  │ │  ╔═══╗  │              │
│  │  ║NEO║  │ │  ║TRI║  │ │  ║MOR║  │              │
│  │  ╚═══╝  │ │  ╚═══╝  │ │  ╚═══╝  │              │
│  │The One  │ │Warrior  │ │The Guide│              │
│  └─────────┘ └─────────┘ └─────────┘              │
│  ┌─────────┐ ┌─────────┐                          │
│  │  ╔═══╗  │ │  ╔═══╗  │                          │
│  │  ║NIO║  │ │  ║GHO║  │                          │
│  │  ╚═══╝  │ │  ╚═══╝  │                          │
│  │Captain  │ │Assassin │                          │
│  └─────────┘ └─────────┘                          │
│                                                     │
│         [ ENTER THE MATRIX ]                        │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### 4.4 CSS Architecture

Add a dedicated game stylesheet: `resources/css/matrix.css` imported into `app.css`.

Key CSS classes:
- `.matrix-bg` — black background with digital rain animation
- `.matrix-text` — green monospace text with subtle glow
- `.matrix-border` — dark green border with glow on hover
- `.matrix-btn` — terminal-style button with green border, glow on hover, click flash
- `.matrix-health-bar` — segmented health bar, green→yellow→red gradient
- `.matrix-scanlines` — CRT scanline overlay
- `.matrix-typing` — typing animation for narrative text
- `.matrix-card` — character/inventory card with terminal border
- `.matrix-glow` — pulsing green glow effect

### 4.5 Digital Rain Effect

A lightweight CSS-only Matrix rain effect for the background of the character select screen. Uses CSS `@keyframes` with random `animation-delay` on column `<span>` elements. Alpine.js generates the columns on mount. This runs behind the UI with reduced opacity so it doesn't distract.

---

## Phase 5: Filament 5 Admin Panel

### 5.1 Setup

After installing Filament 5, create the admin panel provider and theme. Apply a dark Matrix-inspired theme to the admin panel.

### 5.2 Resources

**GameResource** — `app/Filament/Resources/GameResource.php`

```bash
php artisan make:filament-resource Game --generate
```

Table columns:
- ID
- User (relationship: `user.name`)
- Character (badge with color)
- Health (progress bar)
- Status (badge: active=green, completed=blue, game_over=red)
- Turn Count
- Created At

Form fields:
- User select
- Character select
- Health number input
- Inventory JSON editor
- Status select

Filters:
- Character filter
- Status filter
- Date range filter

**UserResource** — only if needed beyond Filament's built-in user management.

### 5.3 Dashboard Widgets

- **Active Games Widget** — count of active games
- **Total Games Widget** — total games played
- **Character Popularity Chart** — bar chart of character selections
- **Average Turns Widget** — average turns per game

---

## Phase 6: Testing

### 6.1 Feature Tests

**Game Creation Test** — `tests/Feature/Game/GameCreationTest.php`
- Authenticated user can view character select page
- Unauthenticated user is redirected to login
- User can create a game with a valid character
- Invalid character is rejected

**Game Play Test** — `tests/Feature/Game/GamePlayTest.php`
- User can view their own game board
- User cannot view another user's game board
- Making a choice updates game state
- Game over when health reaches 0

**Agent Test** — `tests/Feature/Ai/MatrixGameAgentTest.php`
- Agent returns valid structured output (fake the agent)
- Agent maintains conversation context
- Structured output has all required fields

**Image Generation Test** — `tests/Feature/Ai/ImageGenerationTest.php`
- Image generation is queued after each turn (fake Image)

### 6.2 Unit Tests

**Character Enum Test** — `tests/Unit/CharacterTest.php`
- All characters have display names
- All characters have descriptions

**Game Model Test** — `tests/Unit/GameModelTest.php`
- Inventory cast works correctly
- Health boundaries are respected

---

## Phase 7: Polish & Details

### 7.1 Loading States

While the AI agent processes a turn:
- Choices dim and disable
- A terminal-style loading animation appears: `> Processing... ▌` with blinking cursor
- `wire:loading` on the game board component

### 7.2 Game Over Screen

When health reaches 0 or the agent signals game over:
- Screen glitches (CSS animation)
- Red text: `SYSTEM FAILURE` or `CONNECTION TERMINATED`
- Final narrative displayed
- Stats summary: turns survived, items collected
- "Reenter The Matrix" button → character select

### 7.3 Sidebar Navigation

Add a "Play" link to the existing app sidebar that links to `/play`.

### 7.4 Sound (Optional Enhancement)

Not in initial scope. Could add ambient Matrix hum and keystroke sounds later via Alpine.js.

---

## Implementation Order

| Step | Task | Details |
|------|------|---------|
| 1 | Install `laravel/ai` | Composer require, publish config, migrate |
| 2 | Install `filament/filament:^5.0` | Composer require, install panels, create admin user |
| 3 | Create enums | `Character`, `GameStatus` |
| 4 | Create `Game` model + migration | With factory and seeder |
| 5 | Create `MatrixGameAgent` | With structured output, tools, instructions |
| 6 | Create AI tools | `DiceRoll`, `CombatCalculator` |
| 7 | Create `CharacterSelect` Livewire component | Full-page component with character cards |
| 8 | Create `GameBoard` Livewire component | Full-page component with game UI |
| 9 | Build Matrix terminal CSS theme | `matrix.css` with all effects |
| 10 | Build character select view | Blade template with Matrix styling |
| 11 | Build game board view | Blade template with image, narrative, health, inventory, choices |
| 12 | Wire up image generation | Queue-based image gen after each turn |
| 13 | Add routes | `/play` and `/play/{game}` |
| 14 | Add sidebar navigation link | "Play" in the app sidebar |
| 15 | Set up Filament resources | `GameResource` with table, form, filters |
| 16 | Create Filament dashboard widgets | Stats and charts |
| 17 | Write feature tests | Game creation, gameplay, agent, image gen |
| 18 | Write unit tests | Enums, model casts |
| 19 | Run Pint | Format all new code |
| 20 | Final QA | Run all tests, verify UI in browser |

---

## File Manifest (New Files)

```
app/
├── Ai/
│   ├── Agents/
│   │   └── MatrixGameAgent.php
│   └── Tools/
│       ├── DiceRoll.php
│       └── CombatCalculator.php
├── Enums/
│   ├── Character.php
│   └── GameStatus.php
├── Filament/
│   └── Resources/
│       └── GameResource.php (+ pages/)
├── Livewire/
│   ├── CharacterSelect.php
│   └── GameBoard.php
└── Models/
    └── Game.php

database/
├── factories/
│   └── GameFactory.php
├── migrations/
│   └── xxxx_xx_xx_create_games_table.php
└── seeders/
    └── GameSeeder.php

resources/
├── css/
│   └── matrix.css
└── views/
    └── livewire/
        ├── character-select.blade.php
        └── game-board.blade.php

tests/
├── Feature/
│   ├── Ai/
│   │   ├── MatrixGameAgentTest.php
│   │   └── ImageGenerationTest.php
│   └── Game/
│       ├── GameCreationTest.php
│       └── GamePlayTest.php
└── Unit/
    ├── CharacterTest.php
    └── GameModelTest.php
```

---

## Environment Variables Required

```env
OPENAI_API_KEY=sk-...          # Required for AI agent + image generation
```

---

## Constraints & Decisions

1. **Single AI provider (OpenAI)** — supports both text (GPT) and image generation (DALL-E). Simplest setup. Can add failover to Anthropic later.
2. **Queued image generation** — narrative appears instantly; image loads async. No blocking the game loop.
3. **SQLite stays** — lightweight, no external DB needed for a game. Works fine for the scale.
4. **Structured output enforces game rules** — the agent cannot "forget" to include health, inventory, or choices. The schema guarantees it.
5. **RemembersConversations** — the SDK handles all conversation persistence. No custom message storage needed.
6. **Filament 5 for admin only** — the game UI is custom Livewire + Tailwind. Filament manages the backend.
7. **No JavaScript frameworks** — Livewire + Alpine.js handle all interactivity. Zero npm JS dependencies added.
