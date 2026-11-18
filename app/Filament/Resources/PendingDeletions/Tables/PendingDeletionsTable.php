<?php

namespace App\Filament\Resources\PendingDeletions\Tables;

use App\Models\User;
use App\Services\AccountClosingService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PendingDeletionsTable
{
    /**
     * Last day of the grace period for this account.
     */
    protected static function deadline(User $record)
    {
        return $record->delete_requested_at->copy()->addDays(User::DELETION_GRACE_DAYS);
    }

    protected static function hasHistory(User $record): bool
    {
        return (bool) $record->purchases_exists;
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->visibleFrom('md'),

                TextColumn::make('delete_requested_at')
                    ->label('Solicitada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('deadline')
                    ->label('Prazo')
                    ->state(fn (User $record) => static::deadline($record))
                    ->dateTime('d/m/Y')
                    ->visibleFrom('md'),

                TextColumn::make('situation')
                    ->label('Situação')
                    ->badge()
                    ->state(fn (User $record) => static::deadline($record)->isPast() ? 'Prazo vencido' : 'Em carência')
                    ->color(fn (string $state) => $state === 'Prazo vencido' ? 'danger' : 'warning'),

                TextColumn::make('outcome')
                    ->label('Ao encerrar')
                    ->state(fn (User $record) => static::hasHistory($record) ? 'Anonimizada' : 'Apagada')
                    ->tooltip(fn (User $record) => static::hasHistory($record)
                        ? 'Tem compras: os dados pessoais saem e os registros financeiros ficam.'
                        : 'Sem histórico financeiro: a conta é apagada por completo.')
                    ->visibleFrom('md'),
            ])
            ->filters([
                Filter::make('overdue')
                    ->label('Somente prazo vencido')
                    ->query(fn (Builder $query) => $query
                        ->where('delete_requested_at', '<=', now()->subDays(User::DELETION_GRACE_DAYS))),
            ])
            ->recordActions([
                Action::make('closeNow')
                    ->icon('heroicon-o-trash')
                    ->iconButton()
                    ->size(Size::Large)
                    ->color('danger')
                    ->tooltip(fn (User $record) => 'Encerrar a conta de ' . $record->name . ' agora')
                    ->authorize('delete')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record) => 'Encerrar a conta de ' . $record->name . '?')
                    ->modalDescription(fn (User $record) => static::hasHistory($record)
                        ? 'A conta tem compras: os dados pessoais serão apagados e os registros financeiros ficam sem identificação. Não dá para desfazer.'
                        : 'A conta e todos os seus dados serão apagados de forma definitiva. Não dá para desfazer.')
                    ->modalSubmitActionLabel('Encerrar agora')
                    ->action(function (User $record) {
                        $closing = app(AccountClosingService::class);

                        $name   = $record->name;
                        $result = $closing->close($record);

                        Notification::make()
                            ->title($result === AccountClosingService::DELETED
                                ? 'Conta de ' . $name . ' apagada'
                                : 'Conta de ' . $name . ' anonimizada')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('delete_requested_at', 'asc')
            ->emptyStateHeading('Nenhuma exclusão solicitada');
    }
}