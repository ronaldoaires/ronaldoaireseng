<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Service;
use Filament\Widgets\ChartWidget;

class VisitsChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'O que mais é visitado';

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = 'products';

    public static function canView(): bool
    {
        return auth()->user()->isSuperAdmin();
    }

    protected function getFilters(): ?array
    {
        return [
            'products' => 'Ebooks e planilhas',
            'services' => 'Serviços',
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        [$model, $label] = match ($this->filter) {
            'services' => [Service::class, 'name'],
            default    => [Product::class, 'title'],
        };

        $rows = $model::query()
            ->orderByDesc('view_count')
            ->limit(10)
            ->get([$label, 'view_count']);

        return [
            'datasets' => [
                [
                    'label'           => 'Visualizações',
                    'data'            => $rows->pluck('view_count')->map(fn ($v) => (int) $v)->all(),
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => $rows->pluck($label)->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            // Horizontal bars read better with long names.
            'indexAxis' => 'y',
            'plugins'   => ['legend' => ['display' => false]],
        ];
    }
}
