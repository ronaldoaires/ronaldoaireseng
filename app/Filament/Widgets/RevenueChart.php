<?php

namespace App\Filament\Widgets;

use App\Models\Purchase;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Receita nos últimos 12 meses';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()->isSuperAdmin();
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $start = now()->startOfMonth()->subMonths(11);

        // Grouped in PHP so the query runs the same on MySQL and SQLite (tests).
        $totals = Purchase::paid()
            ->where('paid_at', '>=', $start)
            ->get(['amount', 'paid_at'])
            ->groupBy(fn (Purchase $purchase) => $purchase->paid_at->format('Y-m'))
            ->map(fn ($group) => (float) $group->sum('amount'));

        $labels = [];
        $data   = [];

        for ($i = 0; $i < 12; $i++) {
            $month = $start->copy()->addMonths($i);

            $labels[] = $month->translatedFormat('M/y');
            $data[]   = $totals[$month->format('Y-m')] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Receita (R$)',
                    'data'            => $data,
                    'backgroundColor' => '#10b981',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
