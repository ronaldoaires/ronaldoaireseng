<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManagePurchases extends ManageRecords
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Courtesy copies, sales outside the site, support cases: the product
            // shows up in the user's library as if it had been bought.
            Action::make('grant')
                ->label('Liberar acesso')
                ->icon('heroicon-o-gift')
                ->authorize('create', Purchase::class)
                ->modalHeading('Liberar um produto para alguém')
                ->modalDescription('A pessoa precisa ter conta no site. O produto aparece na biblioteca dela na hora.')
                ->schema([
                    Select::make('user_id')
                        ->label('Pessoa')
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search) => User::query()
                            ->where(fn ($query) => $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%"))
                            ->whereNotNull('email')
                            ->limit(20)
                            ->get()
                            ->mapWithKeys(fn (User $user) => [$user->id => "{$user->name} ({$user->email})"])
                            ->all())
                        ->getOptionLabelUsing(fn ($value) => User::find($value)?->email)
                        ->required(),

                    Select::make('product_id')
                        ->label('Produto')
                        ->options(fn () => Product::query()->orderBy('title')->pluck('title', 'id'))
                        ->searchable()
                        ->required(),

                    TextInput::make('notes')
                        ->label('Motivo')
                        ->placeholder('Ex: cortesia, venda presencial, troca de e-mail')
                        ->maxLength(255),
                ])
                ->action(function (array $data) {
                    $purchase = Purchase::firstOrNew([
                        'user_id'    => $data['user_id'],
                        'product_id' => $data['product_id'],
                    ]);

                    if ($purchase->exists && $purchase->isPaid()) {
                        Notification::make()->title('Essa pessoa já tem acesso a este produto.')->warning()->send();

                        return;
                    }

                    $purchase->fill([
                        'amount'         => 0,
                        'status'         => 'paid',
                        'source'         => Purchase::SOURCE_MANUAL,
                        'payment_id'     => null,
                        'payment_method' => null,
                        'paid_at'        => now(),
                        'notes'          => $data['notes'] ?? null,
                    ])->save();

                    Notification::make()->title('Acesso liberado')->success()->send();
                }),
        ];
    }
}
