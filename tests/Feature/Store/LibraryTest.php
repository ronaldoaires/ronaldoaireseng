<?php

use App\Models\ProductChapter;
use App\Models\ProductDownload;
use Illuminate\Support\Facades\Storage;

it('shows only paid products in the library', function () {
    $user = makeBuyer();
    grantPurchase($user, makeProduct(['title' => 'Comprado']));
    grantPurchase($user, makeProduct(['title' => 'Aguardando Pix']), 'pending');

    $this->actingAs($user)
        ->get('/conta/biblioteca')
        ->assertOk()
        ->assertSee('Comprado')
        ->assertDontSee('Aguardando Pix');
});

it('downloads a purchased file from the private disk and logs it', function () {
    Storage::fake('local');

    $user    = makeBuyer();
    $product = makeProduct(['slug' => 'concreto-armado']);
    $file    = attachPdf($product);
    grantPurchase($user, $product);

    expect($file->fresh()->size_bytes)->toBeGreaterThan(0);

    $this->actingAs($user)
        ->get(route('account.files.download', ['file' => $file->id]))
        ->assertOk()
        ->assertDownload('concreto-armado.pdf');

    expect(ProductDownload::where('user_id', $user->id)->count())->toBe(1);
});

it('never serves a file to someone who did not buy it', function () {
    Storage::fake('local');

    $product = makeProduct();
    $file    = attachPdf($product);

    $this->actingAs(makeBuyer())
        ->get(route('account.files.download', ['file' => $file->id]))
        ->assertNotFound();

    $this->actingAs(makeBuyer())
        ->get(route('account.library.show', ['product' => $product->slug]))
        ->assertNotFound();
});

it('opens the online reader and remembers where the buyer stopped', function () {
    $user    = makeBuyer();
    $product = makeProduct(['slug' => 'concreto-armado']);
    $first   = ProductChapter::create(['product_id' => $product->id, 'title' => 'Introdução', 'slug' => 'introducao', 'body' => '<p>Um</p>', 'sort_order' => 1]);
    $second  = ProductChapter::create(['product_id' => $product->id, 'title' => 'Vigas', 'slug' => 'vigas', 'body' => '<p>Dois</p>', 'sort_order' => 2]);
    $purchase = grantPurchase($user, $product);

    $this->actingAs($user)
        ->get('/conta/biblioteca/concreto-armado/ler')
        ->assertRedirect('/conta/biblioteca/concreto-armado/ler/introducao');

    $this->actingAs($user)
        ->get('/conta/biblioteca/concreto-armado/ler/vigas')
        ->assertOk()
        ->assertSee('Dois')
        ->assertSee('Capítulo 2 de 2')
        ->assertSee('Licenciado para ' . $user->name);

    expect($purchase->fresh()->last_chapter_id)->toBe($second->id);

    // Next visit without a chapter continues from the last one.
    $this->actingAs($user)
        ->get('/conta/biblioteca/concreto-armado/ler')
        ->assertRedirect('/conta/biblioteca/concreto-armado/ler/vigas');

    $this->actingAs($user)->get('/conta')->assertOk()->assertSee('Continuar lendo');
});

it('keeps a product taken off the store in the buyer library', function () {
    $user    = makeBuyer();
    $product = makeProduct(['slug' => 'edicao-antiga']);
    grantPurchase($user, $product);

    $product->delete();

    $this->get('/ebooks/edicao-antiga')->assertNotFound();

    $this->actingAs($user)
        ->get('/conta/biblioteca/edicao-antiga')
        ->assertOk()
        ->assertSee('não está mais à venda');
});

it('lists every payment attempt in the purchase history', function () {
    $user = makeBuyer();
    grantPurchase($user, makeProduct(['title' => 'Planilha X']), 'failed');

    $this->actingAs($user)->get('/conta/compras')->assertOk()->assertSee('Planilha X')->assertSee('Falhou');
});
