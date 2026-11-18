<?php

namespace App\Filament\Resources\Products\Pages;

use App\Enums\ProductType;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }

    /**
     * One tab per type: ebooks and spreadsheets stay apart in a single resource.
     */
    public function getTabs(): array
    {
        $tabs = ['all' => Tab::make('Todos')];

        foreach (ProductType::cases() as $type) {
            $tabs[$type->value] = Tab::make($type->pluralLabel())
                ->badge(Product::query()->ofType($type)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->ofType($type));
        }

        return $tabs;
    }
}
