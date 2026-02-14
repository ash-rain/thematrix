<?php

namespace App\Filament\Resources\Games\Schemas;

use App\Enums\Character;
use App\Enums\GameStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('character')
                    ->options(Character::class)
                    ->required(),
                TextInput::make('health')
                    ->required()
                    ->numeric()
                    ->default(100),
                Textarea::make('inventory')
                    ->required()
                    ->default('[]')
                    ->columnSpanFull(),
                TextInput::make('conversation_id'),
                Select::make('status')
                    ->options(GameStatus::class)
                    ->default('active')
                    ->required(),
                TextInput::make('turn_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                FileUpload::make('scene_image_path')
                    ->image(),
            ]);
    }
}
