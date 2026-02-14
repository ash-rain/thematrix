<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CombatCalculator implements Tool
{
    public function description(): Stringable|string
    {
        return 'Calculate combat damage between an attacker and defender. Returns damage dealt and received based on combat dynamics.';
    }

    public function handle(Request $request): Stringable|string
    {
        $attacker = $request['attacker'];
        $defender = $request['defender'];
        $weapon = $request['weapon'] ?? 'fists';

        $weaponMultipliers = [
            'fists' => 1.0,
            'pistol' => 1.5,
            'twin pistols' => 1.8,
            'silenced pistol' => 1.4,
            'rifle' => 2.0,
            'combat knife' => 1.3,
            'katana' => 2.2,
            'kung fu' => 1.6,
            'rocket launcher' => 3.0,
        ];

        $multiplier = $weaponMultipliers[strtolower($weapon)] ?? 1.2;

        $baseDamage = random_int(5, 25);
        $damageDealt = (int) round($baseDamage * $multiplier);
        $damageReceived = random_int(0, 15);

        return json_encode([
            'attacker' => $attacker,
            'defender' => $defender,
            'weapon' => $weapon,
            'damage_dealt' => $damageDealt,
            'damage_received' => $damageReceived,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'attacker' => $schema->string()->required(),
            'defender' => $schema->string()->required(),
            'weapon' => $schema->string(),
        ];
    }
}
