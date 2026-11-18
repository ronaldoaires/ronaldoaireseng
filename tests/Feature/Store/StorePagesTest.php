<?php

use App\Enums\ProductType;
use App\Models\ProductChapter;

it('lists published products under their own prefix', function () {
    makeProduct(['title' => 'Concreto Armado na Prática', 'slug' => 'concreto-armado']);
    makeProduct(['title' => 'Rascunho', 'slug' => 'rascunho', 'is_published' => false]);
    makeProduct(['title' => 'Armei Vigas', 'slug' => 'armei-vigas', 'type' => ProductType::Spreadsheet, 'has_reader' => false]);

    $this->get('/ebooks')
        ->assertOk()
        ->assertSee('Concreto Armado na Prática')
        ->assertDontSee('Rascunho')
        ->assertDontSee('Armei Vigas');

    $this->get('/planilhas')->assertOk()->assertSee('Armei Vigas');
});

it('shows an empty state when there is nothing to sell yet', function () {
    $this->get('/planilhas')->assertOk()->assertSee('Em breve');
});

it('renders the sales page with price, structured data and buy button', function () {
    makeProduct([
        'title'            => 'Concreto Armado na Prática',
        'slug'             => 'concreto-armado',
        'price'            => 79.90,
        'compare_at_price' => 129.90,
        'highlights'       => ['12 exemplos resolvidos'],
        'faq'              => [['question' => 'Serve para NBR 6118:2023?', 'answer' => 'Sim.']],
    ]);

    $this->get('/ebooks/concreto-armado')
        ->assertOk()
        ->assertSee('R$ 79,90')
        ->assertSee('R$ 129,90')
        ->assertSee('12 exemplos resolvidos')
        ->assertSee('"@type":"Product"', false)
        ->assertSee('"@type":"FAQPage"', false)
        ->assertSee(route('checkout.show', ['product' => 'concreto-armado']), false);
});

it('redirects a product opened under the wrong prefix to its canonical URL', function () {
    makeProduct(['slug' => 'armei-vigas', 'type' => ProductType::Spreadsheet]);

    $this->get('/ebooks/armei-vigas')->assertRedirect('/planilhas/armei-vigas')->assertStatus(301);
});

it('hides unpublished products', function () {
    makeProduct(['slug' => 'rascunho', 'is_published' => false]);

    $this->get('/ebooks/rascunho')->assertNotFound();
});

it('opens only the chapters marked as free sample', function () {
    $product = makeProduct(['slug' => 'concreto-armado']);
    $free    = ProductChapter::create(['product_id' => $product->id, 'title' => 'Introdução', 'slug' => 'introducao', 'body' => '<p>Texto livre</p>', 'is_preview' => true]);
    ProductChapter::create(['product_id' => $product->id, 'title' => 'Lajes', 'slug' => 'lajes', 'body' => '<p>Pago</p>', 'sort_order' => 1]);

    $this->get('/ebooks/concreto-armado/amostra/introducao')->assertOk()->assertSee('Texto livre');
    $this->get('/ebooks/concreto-armado/amostra/lajes')->assertNotFound();

    expect($free->readingMinutes())->toBe(1);
});

it('lists products and sample chapters in the sitemap', function () {
    $product = makeProduct(['slug' => 'concreto-armado']);
    ProductChapter::create(['product_id' => $product->id, 'title' => 'Introdução', 'slug' => 'introducao', 'is_preview' => true]);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee(url('/ebooks'), false)
        ->assertSee(url('/ebooks/concreto-armado'), false)
        ->assertSee(url('/ebooks/concreto-armado/amostra/introducao'), false);
});

it('shows the featured products on the home page', function () {
    makeProduct(['title' => 'Concreto Armado na Prática', 'is_featured' => true]);

    $this->get('/')->assertOk()->assertSee('Concreto Armado na Prática')->assertSee('id="materiais"', false);
});
