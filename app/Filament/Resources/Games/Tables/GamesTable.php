<?php

namespace App\Filament\Resources\Games\Tables;

use App\Enums\Character;
use App\Enums\GameStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GamesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Player')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('character')
                    ->badge()
                    ->color(fn (Character $state): string => match ($state) {
                        Character::Neo => 'success',
                        Character::Trinity => 'info',
                        Character::Morpheus => 'warning',
                        Character::Niobe => 'primary',
                        Character::Ghost => 'gray',
                    })
                    ->formatStateUsing(fn (Character $state): string => $state->displayName())
                    ->sortable(),
                TextColumn::make('health')
                    ->numeric()
                    ->sortable()
                    ->color(fn (int $state): string => match (true) {
                        $state > 50 => 'success',
                        $state > 25 => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (GameStatus $state): string => match ($state) {
                        GameStatus::Active => 'success',
                        GameStatus::Completed => 'info',
                        GameStatus::GameOver => 'danger',
                    })
                    ->formatStateUsing(fn (GameStatus $state): string => $state->displayName()),
                TextColumn::make('turn_count')
                    ->label('Turns')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('character')
                    ->options(Character::class),
                SelectFilter::make('status')
                    ->options(GameStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
