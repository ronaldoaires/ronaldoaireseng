<?php

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Purchases\Pages\ManagePurchases;
use App\Models\Product;
use App\Models\User;
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
    $this->get('/admin/produtos/' . $product->id . '/edit')->assertOk();
    $this->get('/admin/vendas')->assertOk();
});

it('creates a product with an automatic slug', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'type'    => 'ebook',
            'title'   => 'Concreto Armado na Prática',
            'summary' => 'Do lançamento das cargas ao detalhamento.',
            'price'   => 79.9,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Product::sole()->slug)->toBe('concreto-armado-na-pratica');
});

it('grants a product to a user from the sales page', function () {
    $buyer   = makeBuyer();
    $product = makeProduct();

    Livewire::test(ManagePurchases::class)
        ->callAction('grant', data: ['user_id' => $buyer->id, 'product_id' => $product->id, 'notes' => 'Cortesia'])
        ->assertHasNoActionErrors();

    expect($buyer->fresh()->owns($product))->toBeTrue();
});

it('keeps plain users out of the panel', function () {
    $this->actingAs(makeBuyer())->get('/admin')->assertForbidden();
});
