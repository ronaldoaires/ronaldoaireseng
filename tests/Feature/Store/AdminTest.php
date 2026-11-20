<?php

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Purchases\Pages\ManagePurchases;
use App\Models\Product;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => User::ROLE_SUPERADMIN]);
    $this->actingAs($this->admin);
});

it('renders the store pages of the admin panel', function () {
    $product = makeProduct();

    $this->get('/admin')->assertOk();
    $this->get('/admin/produtos')->assertOk()->assertSee($product->title);
    $this->get('/admin/produtos/create')->assertOk();
    $this->get('/admin/produtos/'.$product->id.'/edit')->assertOk();
    $this->get('/admin/vendas')->assertOk();
});

it('loads an existing ebook cover from a same-origin preview URL', function () {
    Storage::fake('public');
    $path = 'products/concreto-armado-sem-misterio-capa.png';
    Storage::disk('public')->put($path, file_get_contents(database_path('seeders/assets/products/concreto-armado-sem-misterio-capa.png')));
    $product = makeProduct(['slug' => 'concreto-armado-sem-misterio', 'cover_path' => $path]);

    $page = Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()]);
    $upload = $page->instance()->form->getComponentByStatePath('cover_path');

    expect($upload)->toBeInstanceOf(FileUpload::class)
        ->and(array_values($upload->getUploadedFiles())[0]['url'])->toBe('/media/'.$path);

    $this->get('/media/'.$path)->assertOk()->assertHeader('Content-Type', 'image/png');
});

it('creates a product with an automatic slug', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'type' => 'ebook',
            'title' => 'Concreto Armado na Prática',
            'summary' => 'Do lançamento das cargas ao detalhamento.',
            'price' => 79.9,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Product::sole()->slug)->toBe('concreto-armado-na-pratica');
});

it('grants a product to a user from the sales page', function () {
    $buyer = makeBuyer();
    $product = makeProduct();

    Livewire::test(ManagePurchases::class)
        ->callAction('grant', data: ['user_id' => $buyer->id, 'product_id' => $product->id, 'notes' => 'Cortesia'])
        ->assertHasNoActionErrors();

    expect($buyer->fresh()->owns($product))->toBeTrue();
});

it('keeps plain users out of the panel', function () {
    $this->actingAs(makeBuyer())->get('/admin')->assertForbidden();
});
