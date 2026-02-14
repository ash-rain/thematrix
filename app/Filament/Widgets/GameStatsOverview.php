<?php

namespace App\Filament\Widgets;

use App\Enums\Character;
use App\Enums\GameStatus;
use App\Models\Game;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GameStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalGames = Game::count();
        $activeGames = Game::where('status', GameStatus::Active)->count();
        $avgTurns = (int) Game::avg('turn_count');
        $mostPopular = Game::query()
            ->selectRaw('character, count(*) as count')
            ->groupBy('character')
            ->orderByDesc('count')
            ->first();

        return [
            Stat::make('Total Games', $totalGames)
                ->description('All time')
                ->color('success'),
            Stat::make('Active Games', $activeGames)
                ->description('Currently in progress')
                ->color('info'),
            Stat::make('Avg Turns', $avgTurns)
                ->description('Per game')
                ->color('warning'),
            Stat::make('Top Character', $mostPopular ? Character::from($mostPopular->character)->displayName() : 'N/A')
                ->description($mostPopular ? $mostPopular->count.' games' : 'No games yet')
                ->color('primary'),
        ];
    }
}
