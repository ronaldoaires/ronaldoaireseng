<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Support\ContentEditor;
use App\Models\Product;
use App\Models\ProductChapter;
use App\Support\SlugGenerator;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Chapters of the online (digital) version of an ebook.
 */
class ChaptersRelationManager extends RelationManager
{
    protected static string $relationship = 'chapters';

    protected static ?string $title = 'Versão digital (capítulos)';

    protected static ?string $modelLabel = 'capítulo';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof Product && $ownerRecord->hasOnlineReader();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Título do capítulo')
                ->required()
                ->maxLength(150),

            Toggle::make('is_preview')
                ->label('Amostra grátis')
                ->helperText('Qualquer visitante lê este capítulo na página de vendas. Bom para o primeiro capítulo.')
                ->inline(false),

            ContentEditor::make('body', 'products/chapters/content')
                ->label('Conteúdo'),
        ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->state(fn ($rowLoop) => $rowLoop->iteration),

                TextColumn::make('title')
                    ->label('Título'),

                IconColumn::make('is_preview')
                    ->label('Amostra')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Novo capítulo')
                    ->slideOver()
                    ->mutateDataUsing(function (array $data): array {
                        $product = $this->getOwnerRecord();

                        $data['slug'] = SlugGenerator::unique(
                            ProductChapter::class,
                            $data['title'],
                            queryModifier: fn ($query) => $query->where('product_id', $product->id),
                        );
                        // New chapters go to the end.
                        $data['sort_order'] = (int) $product->chapters()->max('sort_order') + 1;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ]);
    }
}
