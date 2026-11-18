<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\ProductFile;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Files the buyer downloads. They live on the private disk and are only
 * served by account.files.download after the purchase is checked.
 */
class FilesRelationManager extends RelationManager
{
    protected static string $relationship = 'files';

    protected static ?string $title = 'Arquivos para download';

    protected static ?string $modelLabel = 'arquivo';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('label')
                ->label('Nome que o comprador vê')
                ->placeholder('Ex: Ebook em PDF, Planilha (Windows), Exemplos resolvidos')
                ->required()
                ->maxLength(120),

            FileUpload::make('path')
                ->label('Arquivo')
                ->disk('local')
                ->directory('products/files')
                ->visibility('private')
                ->storeFileNamesIn('original_name')
                ->acceptedFileTypes([
                    'application/pdf',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel.sheet.macroEnabled.12',
                    'application/vnd.ms-excel',
                    'application/zip',
                    'application/x-zip-compressed',
                    'application/octet-stream',
                ])
                ->maxSize(51200)
                ->helperText('PDF, Excel (.xlsx/.xlsm) ou ZIP, até 50 MB. Nunca fica público: só quem comprou baixa.')
                ->required(),

            Hidden::make('disk')->default('local'),
            Hidden::make('original_name'),

            TextInput::make('version')
                ->label('Versão do arquivo')
                ->placeholder('Ex: 1.2')
                ->maxLength(30),

            TextInput::make('sort_order')
                ->label('Posição')
                ->numeric()
                ->minValue(0)
                ->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('label')
                    ->label('Nome')
                    ->description(fn (ProductFile $record) => $record->original_name),

                TextColumn::make('size_bytes')
                    ->label('Tamanho')
                    ->formatStateUsing(fn (ProductFile $record) => $record->formattedSize()),

                TextColumn::make('version')
                    ->label('Versão')
                    ->placeholder('—'),

                TextColumn::make('downloads_count')
                    ->label('Downloads')
                    ->counts('downloads'),

                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([
                CreateAction::make()->label('Enviar arquivo'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
