<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_thumb_url')
                    ->label(''),

                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record) => $record->trashed() ? 'Fora da loja' : $record->subtitle),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('sales_count')
                    ->label('Vendas')
                    ->counts(['purchases as sales_count' => fn ($query) => $query->paid()])
                    ->sortable(),

                TextColumn::make('view_count')
                    ->label('Visitas')
                    ->numeric()
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('files_count')
                    ->label('Arquivos')
                    ->counts('files')
                    ->visibleFrom('lg'),

                ToggleColumn::make('is_published')
                    ->label('Publicado')
                    ->sortable()
                    // The toggle writes straight to the record, so the policy must be checked by hand.
                    ->disabled(fn (Product $record) => $record->trashed() || ! auth()->user()->can('update', $record))
                    ->afterStateUpdated(fn (Product $record, bool $state) => Notification::make()
                        ->title($record->title . ($state ? ' publicado' : ' despublicado'))
                        ->success()
                        ->send()),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Publicado'),
                TernaryFilter::make('is_featured')->label('Destaque'),
                TrashedFilter::make()->label('Fora da loja'),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->size(Size::Large)
                    ->tooltip(fn (Product $record) => 'Editar ' . $record->title),
            ])
            ->defaultSort('sort_order');
    }
}
