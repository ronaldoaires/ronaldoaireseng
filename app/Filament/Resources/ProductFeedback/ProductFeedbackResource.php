<?php

namespace App\Filament\Resources\ProductFeedback;

use App\Filament\Resources\ProductFeedback\Pages\ManageProductFeedback;
use App\Models\ProductFeedback;
use App\Rules\PlainText;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ProductFeedbackResource extends Resource
{
    protected static ?string $model = ProductFeedback::class;

    protected static ?string $slug = 'avaliacoes';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationLabel = 'Avaliações';

    protected static string|UnitEnum|null $navigationGroup = 'Loja';

    protected static ?string $modelLabel = 'Avaliação';

    protected static ?string $pluralModelLabel = 'Avaliações';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $pending = ProductFeedback::query()->where('type', ProductFeedback::TYPE_REVIEW)->where('status', ProductFeedback::STATUS_PENDING)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', ProductFeedback::TYPE_REVIEW);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('display_name')
                ->label('Nome exibido')
                ->required()
                ->maxLength(100)
                ->rules([new PlainText]),

            Select::make('rating')
                ->label('Nota')
                ->options([1 => '1 estrela', 2 => '2 estrelas', 3 => '3 estrelas', 4 => '4 estrelas', 5 => '5 estrelas'])
                ->required(),

            Textarea::make('content')
                ->label('Texto')
                ->rows(7)
                ->required()
                ->maxLength(1500)
                ->rules([new PlainText]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['product', 'user']))
            ->columns([
                TextColumn::make('created_at')->label('Recebido')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('product.title')->label('Produto')->searchable()->sortable(),
                TextColumn::make('display_name')->label('Pessoa')->searchable()->description(fn (ProductFeedback $record) => $record->user?->email),
                TextColumn::make('rating')->label('Nota')->state(fn (ProductFeedback $record) => $record->rating ? "{$record->rating}/5" : null)->placeholder('—'),
                TextColumn::make('content')->label('Texto')->limit(70)->wrap()->visibleFrom('xl'),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (?string $state) => ProductFeedback::statusOptions()[$state] ?? $state)
                    ->color(fn (?string $state) => match ($state) {
                        ProductFeedback::STATUS_APPROVED => 'success',
                        ProductFeedback::STATUS_REJECTED => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(ProductFeedback::statusOptions()),
                SelectFilter::make('product_id')->label('Produto')->relationship('product', 'title')->searchable()->preload(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->iconButton()
                    ->size(Size::Large)
                    ->tooltip('Aprovar')
                    ->visible(fn (ProductFeedback $record) => $record->status !== ProductFeedback::STATUS_APPROVED)
                    ->authorize('update')
                    ->action(function (ProductFeedback $record) {
                        $record->update(['status' => ProductFeedback::STATUS_APPROVED, 'approved_at' => now()]);
                        Notification::make()->title('Avaliação publicada no produto')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Desaprovar')
                    ->icon('heroicon-o-no-symbol')
                    ->color('warning')
                    ->iconButton()
                    ->size(Size::Large)
                    ->tooltip('Desaprovar ou retirar do site')
                    ->visible(fn (ProductFeedback $record) => $record->status !== ProductFeedback::STATUS_REJECTED)
                    ->authorize('update')
                    ->requiresConfirmation()
                    ->action(function (ProductFeedback $record) {
                        $record->update(['status' => ProductFeedback::STATUS_REJECTED, 'approved_at' => null]);
                        Notification::make()->title('Conteúdo desaprovado')->success()->send();
                    }),

                EditAction::make()->iconButton()->size(Size::Large)->tooltip('Editar texto, nome ou nota'),
                DeleteAction::make()->iconButton()->size(Size::Large)->tooltip('Apagar definitivamente'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProductFeedback::route('/'),
        ];
    }
}
