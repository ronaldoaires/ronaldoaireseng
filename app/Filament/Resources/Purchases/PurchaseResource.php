<?php

namespace App\Filament\Resources\Purchases;

use App\Filament\Resources\Purchases\Pages\ManagePurchases;
use App\Filament\Resources\Purchases\Tables\PurchasesTable;
use App\Models\Purchase;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use UnitEnum;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $slug = 'vendas';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Vendas';

    protected static string|UnitEnum|null $navigationGroup = 'Loja';

    protected static ?string $modelLabel = 'Venda';

    protected static ?string $pluralModelLabel = 'Vendas';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return PurchasesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePurchases::route('/'),
        ];
    }
}
