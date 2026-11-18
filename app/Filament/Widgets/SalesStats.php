<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Purchase;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Vendas';

    public static function canView(): bool
    {
        return auth()->user()->isSuperAdmin();
    }

    protected function getStats(): array
    {
        $month = Purchase::paid()
            ->where('source', Purchase::SOURCE_CHECKOUT)
            ->where('amount', '>', 0)
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()]);

        $revenue = (float) (clone $month)->sum('amount');
        $sales   = (clone $month)->count();

        $pending = Purchase::where('status', 'pending')
            ->where('updated_at', '>=', now()->subDays(3))
            ->count();

        return [
            Stat::make('Receita do mês', Product::money($revenue))
                ->description($sales . ' ' . ($sales === 1 ? 'venda' : 'vendas') . ' pagas')
                ->color('success'),

            Stat::make('Ticket médio', Product::money($sales ? $revenue / $sales : 0))
                ->description('Neste mês'),

            Stat::make('Clientes', Purchase::paid()->distinct('user_id')->count('user_id'))
                ->description('Pessoas com pelo menos um produto'),

            Stat::make('Pagamentos pendentes', $pending)
                ->description('Últimos 3 dias (Pix aguardando)')
                ->color($pending > 0 ? 'warning' : 'gray'),
        ];
    }
}
