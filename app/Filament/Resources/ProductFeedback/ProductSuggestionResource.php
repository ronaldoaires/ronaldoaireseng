<?php

namespace App\Filament\Resources\ProductFeedback;

use App\Filament\Resources\ProductFeedback\Pages\ManageProductSuggestions;
use App\Models\ProductFeedback;
use App\Rules\PlainText;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ProductSuggestionResource extends Resource
{
    protected static ?string $model = ProductFeedback::class;

    protected static ?string $slug = 'sugestoes';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?string $navigationLabel = 'Sugestões';

    protected static string|UnitEnum|null $navigationGroup = 'Loja';

    protected static ?string $modelLabel = 'Sugestão';

    protected static ?string $pluralModelLabel = 'Sugestões';

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', ProductFeedback::TYPE_SUGGESTION);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('display_name')->label('Nome')->required()->maxLength(100)->rules([new PlainText]),
            TextInput::make('subject')->label('Assunto')->required()->maxLength(120)->rules([new PlainText]),
            Textarea::make('content')->label('Sugestão')->rows(7)->required()->maxLength(2000)->rules([new PlainText]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['product', 'user']))
            ->columns([
                TextColumn::make('created_at')->label('Recebida')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('product.title')->label('Produto')->searchable()->sortable(),
                TextColumn::make('display_name')->label('Pessoa')->searchable()->description(fn (ProductFeedback $record) => $record->user?->email),
                TextColumn::make('subject')->label('Assunto')->searchable()->wrap(),
                TextColumn::make('content')->label('Sugestão')->limit(100)->wrap(),
            ])
            ->filters([
                SelectFilter::make('product_id')->label('Produto')->relationship('product', 'title')->searchable()->preload(),
            ])
            ->recordActions([
                EditAction::make()->iconButton()->size(Size::Large)->tooltip('Editar sugestão'),
                DeleteAction::make()->iconButton()->size(Size::Large)->tooltip('Apagar definitivamente'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProductSuggestions::route('/'),
        ];
    }
}
