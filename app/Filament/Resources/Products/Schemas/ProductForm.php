<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductType;
use App\Filament\Support\ContentEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Informações')
                ->columns(2)
                ->components([
                    Select::make('type')
                        ->label('Tipo')
                        ->options(ProductType::class)
                        ->default(ProductType::Ebook)
                        ->required()
                        ->native(false)
                        ->live(),

                    TextInput::make('title')
                        ->label('Título')
                        ->placeholder('Ex: Concreto Armado na Prática')
                        ->helperText('O endereço da página (/ebooks/concreto-armado-na-pratica) é criado a partir do título e não muda depois.')
                        ->required()
                        ->maxLength(150),

                    TextInput::make('subtitle')
                        ->label('Subtítulo')
                        ->placeholder('Ex: Do lançamento das cargas ao detalhamento, com exemplos resolvidos')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Textarea::make('summary')
                        ->label('Resumo')
                        ->helperText('Aparece nos cards, no checkout e no Google quando o campo de SEO estiver vazio. Até 300 caracteres.')
                        ->required()
                        ->maxLength(300)
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Section::make('Preço')
                ->columns(2)
                ->components([
                    TextInput::make('price')
                        ->label('Preço')
                        ->prefix('R$')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->required()
                        ->helperText('Use 0 para um material gratuito (a pessoa só precisa criar a conta).'),

                    TextInput::make('compare_at_price')
                        ->label('Preço "de"')
                        ->prefix('R$')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->gt('price')
                        ->helperText('Opcional. Mostra "de R$ X por R$ Y" quando for maior que o preço.'),
                ]),

            Section::make('Capa')
                ->components([
                    // Raw upload (storeFiles(false)): the Create/Edit pages run it
                    // through ImageService (webp + thumbnail).
                    FileUpload::make('cover_path')
                        ->label('Capa')
                        ->image()
                        ->imageEditor()
                        ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                        ->maxSize(5120)
                        ->disk('public')
                        ->visibility('public')
                        ->storeFiles(false)
                        ->helperText('PNG, JPG ou WebP, até 5 MB. Use a proporção de capa de livro (3:4), ex. 1200 × 1600 px.'),
                ]),

            Section::make('Página de vendas')
                ->components([
                    ContentEditor::make('body', 'products/content')
                        ->label('Texto')
                        ->helperText('Para quem é, qual problema resolve, o que tem dentro e por que confiar em você.'),
                ]),

            Section::make('O que você recebe')
                ->description('Lista com marcadores logo abaixo do título.')
                ->components([
                    Repeater::make('highlights')
                        ->label('Itens')
                        ->simple(
                            TextInput::make('item')
                                ->placeholder('Ex: 12 exemplos resolvidos passo a passo')
                                ->required()
                                ->maxLength(150),
                        )
                        ->addActionLabel('Adicionar item')
                        ->reorderable()
                        ->defaultItems(0),
                ]),

            Section::make('Ficha técnica')
                ->description('Ex: Páginas · 180 / Formato · PDF + leitura online / Compatível · Excel 2016+ (Windows). Uma linha com o item "Norma" (ex.: NBR 6118:2023) aparece como etiqueta no card da loja.')
                ->collapsible()
                ->components([
                    Repeater::make('specs')
                        ->label('Linhas')
                        ->schema([
                            TextInput::make('label')
                                ->label('Item')
                                ->required()
                                ->maxLength(60),

                            TextInput::make('value')
                                ->label('Valor')
                                ->required()
                                ->maxLength(150),
                        ])
                        ->columns(2)
                        ->addActionLabel('Adicionar linha')
                        ->reorderable()
                        ->defaultItems(0),
                ]),

            Section::make('Perguntas frequentes')
                ->description('Também viram resultado rico no Google (FAQ).')
                ->collapsible()
                ->collapsed()
                ->components([
                    Repeater::make('faq')
                        ->label('Perguntas')
                        ->schema([
                            TextInput::make('question')
                                ->label('Pergunta')
                                ->required()
                                ->maxLength(200),

                            Textarea::make('answer')
                                ->label('Resposta')
                                ->required()
                                ->maxLength(1000)
                                ->rows(3),
                        ])
                        ->itemLabel(fn (array $state) => $state['question'] ?? null)
                        ->addActionLabel('Adicionar pergunta')
                        ->collapsible()
                        ->reorderable()
                        ->defaultItems(0),
                ]),

            Section::make('SEO')
                ->description('Opcional. Em branco, usa o título e o resumo.')
                ->collapsible()
                ->collapsed()
                ->columns(2)
                ->components([
                    TextInput::make('seo_title')
                        ->label('Título no Google')
                        ->maxLength(70)
                        ->helperText('Até 70 caracteres.'),

                    Textarea::make('seo_description')
                        ->label('Descrição no Google')
                        ->maxLength(160)
                        ->rows(2)
                        ->helperText('Até 160 caracteres.'),
                ]),

            Section::make('Publicação')
                ->columns(2)
                ->components([
                    Toggle::make('is_published')
                        ->label('Publicado no site')
                        ->helperText('Só publique depois de enviar os arquivos.')
                        ->inline(false),

                    Toggle::make('is_featured')
                        ->label('Destaque na home')
                        ->inline(false),

                    Toggle::make('has_reader')
                        ->label('Versão digital (leitura online)')
                        ->helperText('Quem compra lê os capítulos no site, além de baixar o PDF. Cadastre os capítulos na aba abaixo.')
                        ->visible(fn (Get $get) => in_array($get('type'), [ProductType::Ebook, ProductType::Ebook->value], true))
                        ->inline(false),

                    TextInput::make('version')
                        ->label('Versão')
                        ->placeholder('Ex: 1.0 ou 2ª edição')
                        ->maxLength(30),

                    TextInput::make('sort_order')
                        ->label('Posição')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->helperText('Menor aparece primeiro.'),
                ]),
        ]);
    }
}
