<?php

namespace App\Filament\Resources\Purchases\Tables;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Purchase;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Query\Builder;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['user', 'product']))
            ->columns([
                TextColumn::make('paid_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Não pago')
                    ->description(fn (Purchase $record) => 'Registrado em ' . $record->created_at?->format('d/m/Y'))
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Comprador')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Purchase $record) => $record->user?->email),

                TextColumn::make('product.title')
                    ->label('Produto')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('amount')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable()
                    // Footer total counts only paid purchases and follows the active filters.
                    ->summarize(
                        Sum::make()
                            ->label('Total pago')
                            ->money('BRL')
                            ->query(fn (Builder $query) => $query->where('status', 'paid'))
                    ),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => Purchase::statusOptions()[$state] ?? $state)
                    ->color(fn (?string $state) => match ($state) {
                        'paid'     => 'success',
                        'pending'  => 'warning',
                        'refunded' => 'info',
                        'failed'   => 'danger',
                        default    => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Forma')
                    ->state(fn (Purchase $record) => $record->paymentMethodLabel())
                    ->description(fn (Purchase $record) => $record->notes)
                    ->visibleFrom('md'),

                TextColumn::make('payment_id')
                    ->label('ID no Mercado Pago')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(Purchase::statusOptions()),

                SelectFilter::make('source')
                    ->label('Origem')
                    ->options([
                        Purchase::SOURCE_CHECKOUT => 'Site',
                        Purchase::SOURCE_MANUAL   => 'Liberado no painel',
                    ]),

                SelectFilter::make('product_id')
                    ->label('Produto')
                    ->relationship('product', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('product')
                    ->icon('heroicon-o-book-open')
                    ->iconButton()
                    ->size(Size::Large)
                    ->tooltip(fn (Purchase $record) => 'Abrir ' . $record->product?->title)
                    ->visible(fn (Purchase $record) => $record->product !== null)
                    ->url(fn (Purchase $record) => ProductResource::getUrl('edit', ['record' => $record->product])),

                // Only access granted in the panel can be taken back here; paid
                // purchases follow Mercado Pago (refunds arrive by webhook).
                Action::make('revoke')
                    ->icon('heroicon-o-no-symbol')
                    ->iconButton()
                    ->size(Size::Large)
                    ->color('danger')
                    ->tooltip('Revogar acesso')
                    ->visible(fn (Purchase $record) => $record->isManual() && $record->isPaid())
                    ->authorize('update')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Purchase $record) => 'Revogar o acesso de ' . $record->user?->name . '?')
                    ->action(function (Purchase $record) {
                        $record->update(['status' => 'cancelled']);

                        Notification::make()->title('Acesso revogado')->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
