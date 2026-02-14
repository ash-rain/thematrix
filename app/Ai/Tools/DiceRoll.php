<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class DiceRoll implements Tool
{
    public function description(): Stringable|string
    {
        return 'Roll dice for skill checks, combat, or random events. Returns the total and individual rolls.';
    }

    public function handle(Request $request): Stringable|string
    {
        $sides = $request['sides'] ?? 20;
        $count = $request['count'] ?? 1;

        $rolls = [];
        for ($i = 0; $i < $count; $i++) {
            $rolls[] = random_int(1, $sides);
        }

        $total = array_sum($rolls);

        return json_encode([
            'rolls' => $rolls,
            'total' => $total,
            'sides' => $sides,
            'count' => $count,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'sides' => $schema->integer()->required(),
            'count' => $schema->integer()->required(),
        ];
    }
}
