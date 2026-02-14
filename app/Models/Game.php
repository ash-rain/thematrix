<?php

namespace App\Models;

use App\Enums\Character;
use App\Enums\GameStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Game extends Model
{
    /** @use HasFactory<\Database\Factories\GameFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'character',
        'health',
        'inventory',
        'conversation_id',
        'status',
        'turn_count',
        'scene_image_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'character' => Character::class,
            'status' => GameStatus::class,
            'inventory' => 'array',
            'health' => 'integer',
            'turn_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === GameStatus::Active;
    }

    public function isGameOver(): bool
    {
        return $this->status === GameStatus::GameOver || $this->health <= 0;
    }
}
