<?php

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Support\Facades\Storage;

it('registers the published ebook with real private delivery and public sample only once', function () {
    Storage::fake('public');
    Storage::fake('local');

    $this->seed(ProductSeeder::class);
    $this->seed(ProductSeeder::class);

    $product = Product::where('slug', 'concreto-armado-sem-misterio')->firstOrFail();

    expect(Product::where('slug', $product->slug)->count())->toBe(1)
        ->and((float) $product->price)->toBe(59.9)
        ->and($product->is_published)->toBeTrue()
        ->and($product->files()->count())->toBe(1)
        ->and(Storage::disk('public')->exists($product->preview_pdf_path))->toBeTrue()
        ->and(Storage::disk('local')->exists($product->files->first()->path))->toBeTrue()
        ->and(array_column($product->preview_images, 'path'))->toBe(array_map(
            fn (int $page): string => "products/previews/amostra-{$page}.jpg",
            [10, 11, 12, 13, 19, 26, 41, 42, 47, 57, 72, 123, 124, 128, 129],
        ))
        ->and(count($product->sales_reasons))->toBe(3);

    $this->get($product->url)
        ->assertOk()
        ->assertSee('amostra-26.jpg')
        ->assertSee('amostra-19.jpg')
        ->assertSee('amostra-47.jpg')
        ->assertSee('amostra-57.jpg')
        ->assertSee('amostra-72.jpg')
        ->assertDontSee('amostra-1.jpg')
        ->assertDontSee('amostra-5.jpg')
        ->assertSee('data-carousel-shuffle', false)
        ->assertSee('aria-label="Ampliar:', false)
        ->assertDontSee('Página 1 de 15')
        ->assertSee('data-carousel-finite', false)
        ->assertSee('data-modal-open=', false)
        ->assertSee('Por que adquirir')
        ->assertSee('Possuirei acesso vitalício?');
});

it('refreshes the earlier seed copy while preserving a price edited in the admin', function () {
    Storage::fake('public');
    Storage::fake('local');

    $product = makeProduct([
        'slug' => 'concreto-armado-sem-misterio',
        'price' => 74.90,
        'summary' => 'Entenda o caminho das cargas, os materiais, as peças estruturais e as decisões da obra em uma leitura visual, direta e fundamentada nas normas.',
        'body' => '<p>O livro organiza essas respostas em uma sequência fácil de acompanhar.</p>',
        'preview_images' => [
            ['path' => 'products/previews/amostra-10.jpg', 'alt' => 'Página 10'],
            ['path' => 'products/previews/amostra-41.jpg', 'alt' => 'Página 41'],
            ['path' => 'products/previews/amostra-123.jpg', 'alt' => 'Página 123'],
        ],
    ]);

    $this->seed(ProductSeeder::class);

    $product->refresh();

    expect((float) $product->price)->toBe(74.9)
        ->and($product->summary)->toContain('principais dúvidas da obra')
        ->and($product->body)->toContain('responde às principais dúvidas da obra')
        ->and($product->preview_images[0]['path'])->toBe('products/previews/amostra-10.jpg')
        ->and($product->faq)->toContainEqual(['question' => 'Possuirei acesso vitalício?', 'answer' => 'Sim. O ebook permanece na sua biblioteca sem prazo de expiração enquanto sua conta estiver ativa; você pode ler online e baixar o PDF quando precisar. Melhorias e atualizações de normas incorporadas a esta edição são disponibilizadas sem custo adicional. Novas edições podem ser vendidas separadamente.']);
});

it('updates the current preview to book order while preserving an edited price', function () {
    Storage::fake('public');
    Storage::fake('local');

    $product = makeProduct([
        'slug' => 'concreto-armado-sem-misterio',
        'price' => 74.90,
        'preview_images' => array_map(
            fn (int $page): array => ['path' => "products/previews/amostra-{$page}.jpg", 'alt' => "Página {$page}"],
            [128, 129, 1, 3, 4, 5, 10, 11, 12, 13, 41, 42, 123, 124],
        ),
    ]);

    $this->seed(ProductSeeder::class);

    $product->refresh();

    expect((float) $product->price)->toBe(74.9)
        ->and(array_column($product->preview_images, 'path'))->toBe(array_map(
            fn (int $page): string => "products/previews/amostra-{$page}.jpg",
            [10, 11, 12, 13, 19, 26, 41, 42, 47, 57, 72, 123, 124, 128, 129],
        ));
});

it('refreshes the previously ordered preview without changing admin pricing', function () {
    Storage::fake('public');
    Storage::fake('local');

    $product = makeProduct([
        'slug' => 'concreto-armado-sem-misterio',
        'price' => 74.90,
        'preview_images' => array_map(
            fn (int $page): array => ['path' => "products/previews/amostra-{$page}.jpg", 'alt' => "Página {$page}"],
            [1, 3, 4, 5, 10, 11, 12, 13, 26, 41, 42, 123, 124, 128, 129],
        ),
    ]);

    $this->seed(ProductSeeder::class);

    $product->refresh();

    expect((float) $product->price)->toBe(74.9)
        ->and(array_column($product->preview_images, 'path'))->toBe(array_map(
            fn (int $page): string => "products/previews/amostra-{$page}.jpg",
            [10, 11, 12, 13, 19, 26, 41, 42, 47, 57, 72, 123, 124, 128, 129],
        ));
});

it('replaces the author page in the current sample with printed page 14', function () {
    Storage::fake('public');
    Storage::fake('local');

    $product = makeProduct([
        'slug' => 'concreto-armado-sem-misterio',
        'preview_images' => array_map(
            fn (int $page): array => ['path' => "products/previews/amostra-{$page}.jpg", 'alt' => "Página {$page}"],
            [5, 10, 11, 12, 13, 26, 41, 42, 47, 57, 72, 123, 124, 128, 129],
        ),
    ]);

    $this->seed(ProductSeeder::class);

    expect(array_column($product->fresh()->preview_images, 'path'))->toBe(array_map(
        fn (int $page): string => "products/previews/amostra-{$page}.jpg",
        [10, 11, 12, 13, 19, 26, 41, 42, 47, 57, 72, 123, 124, 128, 129],
    ));
});
