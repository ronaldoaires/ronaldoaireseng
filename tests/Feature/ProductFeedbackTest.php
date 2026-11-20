<?php

use App\Enums\ProductType;
use App\Filament\Resources\ProductFeedback\Pages\ManageProductFeedback;
use App\Filament\Resources\ProductFeedback\Pages\ManageProductSuggestions;
use App\Models\ProductFeedback;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

it('accepts one buyer review with a rating and keeps it hidden until approval', function () {
    $buyer = makeBuyer(['name' => 'Ana Maria']);
    $product = makeProduct(['slug' => 'livro-avaliado']);
    grantPurchase($buyer, $product);

    $form = route('products.feedback.review', ['type' => 'ebooks', 'product' => $product->slug]);
    $this->get($form)->assertRedirect(route('login'));

    $this->actingAs($buyer)->get($form)->assertOk()->assertSee('Conte como foi sua experiência');

    $this->actingAs($buyer)->post($form, [
        'display_name' => 'Ana Maria',
        'rating' => 5,
        'content' => 'As explicações sobre vigas ajudaram muito na leitura do projeto.',
    ])->assertRedirect(route('account.library.show', ['product' => $product->slug]).'#feedback-heading')
        ->assertSessionHas('feedback_status');

    $this->get(route('account.library.show', ['product' => $product->slug]))
        ->assertSee('Obrigado! Sua avaliação foi recebida.');

    $review = ProductFeedback::sole();
    expect($review->status)->toBe(ProductFeedback::STATUS_PENDING)
        ->and($review->rating)->toBe(5);

    $this->get($product->url)->assertOk()->assertDontSee($review->content);

    $this->actingAs(makeBuyer())->post($form, [
        'display_name' => 'Outra Pessoa',
        'rating' => 5,
        'content' => 'Ainda não comprei, então não devo publicar uma avaliação.',
    ])->assertForbidden();
});

it('validates the stars and sends an edited review back to moderation', function () {
    $buyer = makeBuyer();
    $product = makeProduct();
    grantPurchase($buyer, $product);
    $url = route('products.feedback.review.store', ['type' => 'ebooks', 'product' => $product->slug]);

    $this->actingAs($buyer)->post($url, [
        'display_name' => $buyer->name,
        'rating' => 6,
        'content' => 'Conteúdo com mais de vinte caracteres.',
    ])->assertSessionHasErrors('rating');

    $review = ProductFeedback::create([
        'product_id' => $product->id,
        'user_id' => $buyer->id,
        'review_user_id' => $buyer->id,
        'type' => ProductFeedback::TYPE_REVIEW,
        'status' => ProductFeedback::STATUS_APPROVED,
        'approved_at' => now(),
        'display_name' => $buyer->name,
        'rating' => 4,
        'content' => 'Primeira avaliação aprovada sobre o material.',
    ]);

    $this->actingAs($buyer)->post($url, [
        'display_name' => 'Ana Teste',
        'rating' => 3,
        'content' => 'Depois de reler, quero atualizar minha avaliação do material.',
    ])->assertRedirect();

    expect(ProductFeedback::count())->toBe(1)
        ->and($review->fresh()->status)->toBe(ProductFeedback::STATUS_PENDING)
        ->and($review->fresh()->approved_at)->toBeNull();
});

it('receives multiple private suggestions for ebooks and spreadsheets', function () {
    $user = makeBuyer();
    $ebook = makeProduct();
    $spreadsheet = makeProduct(['type' => ProductType::Spreadsheet, 'slug' => 'planilha-ideias']);

    foreach ([$ebook, $spreadsheet] as $product) {
        grantPurchase($user, $product);

        $url = route('products.feedback.suggestion.store', [
            'type' => $product->type->segment(),
            'product' => $product->slug,
        ]);

        $this->actingAs($user)->post($url, [
            'display_name' => $user->name,
            'subject' => 'Mais exemplos práticos',
            'content' => 'Gostaria de ver mais exemplos aplicados em obras reais.',
        ])->assertRedirect(route('account.library.show', ['product' => $product->slug]).'#feedback-heading')
            ->assertSessionHas('feedback_status');

        $this->get(route('account.library.show', ['product' => $product->slug]))
            ->assertSee('Obrigado! Sua sugestão foi recebida');
    }

    expect(ProductFeedback::where('type', ProductFeedback::TYPE_SUGGESTION)->count())->toBe(2);

    ProductFeedback::query()->update(['status' => ProductFeedback::STATUS_APPROVED, 'approved_at' => now()]);
    $this->get($ebook->url)->assertDontSee('Gostaria de ver mais exemplos aplicados');
});

it('returns a suggestion from a nonbuyer to the product instead of an inaccessible library', function () {
    $user = makeBuyer();
    $product = makeProduct();

    $this->actingAs($user)->post(route('products.feedback.suggestion.store', ['type' => 'ebooks', 'product' => $product->slug]), [
        'display_name' => $user->name,
        'subject' => 'Mais exemplos práticos',
        'content' => 'Gostaria de ver mais exemplos aplicados em obras reais.',
    ])->assertRedirect($product->url.'#avaliacoes');

    $this->get($product->url)->assertSee('Obrigado! Sua sugestão foi recebida');
});

it('rejects HTML and JavaScript in reviews and suggestions', function () {
    $buyer = makeBuyer();
    $product = makeProduct(['type' => ProductType::Spreadsheet, 'slug' => 'planilha-avaliada']);
    grantPurchase($buyer, $product);

    $url = route('products.feedback.review.store', ['type' => 'planilhas', 'product' => $product->slug]);

    $this->actingAs($buyer)->post($url, [
        'display_name' => $buyer->name,
        'rating' => 4,
        'content' => '<script>alert(1)</script> A planilha facilitou minha conferência.',
    ])->assertSessionHasErrors('content');

    $this->actingAs($buyer)->post(route('products.feedback.suggestion.store', ['type' => 'planilhas', 'product' => $product->slug]), [
        'display_name' => $buyer->name,
        'subject' => 'Mais exemplos práticos',
        'content' => '&lt;img src=x onerror=alert(1)&gt; Gostaria de mais exemplos práticos.',
    ])->assertSessionHasErrors('content');

    $this->actingAs($buyer)->post(route('products.feedback.suggestion.store', ['type' => 'planilhas', 'product' => $product->slug]), [
        'display_name' => $buyer->name,
        'subject' => '<script>alert(1)</script> Novo assunto',
        'content' => 'Gostaria de ver mais exemplos práticos neste material.',
    ])->assertSessionHasErrors('subject');

    expect(ProductFeedback::count())->toBe(0);
});

it('renders previously approved reviews as safe text and marks them for search', function () {
    $buyer = makeBuyer();
    $product = makeProduct();
    ProductFeedback::create([
        'product_id' => $product->id,
        'user_id' => $buyer->id,
        'review_user_id' => $buyer->id,
        'type' => ProductFeedback::TYPE_REVIEW,
        'status' => ProductFeedback::STATUS_APPROVED,
        'approved_at' => now(),
        'display_name' => $buyer->name,
        'rating' => 4,
        'content' => '<script>alert(1)</script> O material me ajudou a entender as vigas.',
    ]);

    $this->get($product->url)
        ->assertOk()
        ->assertSee('O material me ajudou a entender as vigas.')
        ->assertDontSee('alert(1)')
        ->assertDontSee('em 1 avaliação aprovada')
        ->assertSee('"@type":"Review"', false)
        ->assertSee('"@type":"AggregateRating"', false);
});

it('lets the superadmin approve, edit, reject and delete feedback', function () {
    $buyer = makeBuyer();
    $product = makeProduct();
    grantPurchase($buyer, $product);
    $review = ProductFeedback::create([
        'product_id' => $product->id,
        'user_id' => $buyer->id,
        'review_user_id' => $buyer->id,
        'type' => ProductFeedback::TYPE_REVIEW,
        'status' => ProductFeedback::STATUS_PENDING,
        'display_name' => 'Ana Teste',
        'rating' => 5,
        'content' => 'O material explica a obra de maneira clara e direta.',
    ]);

    $admin = User::factory()->create(['role' => User::ROLE_SUPERADMIN]);
    $this->actingAs($admin)->get('/admin/avaliacoes')->assertOk();

    Livewire::test(ManageProductFeedback::class)
        ->callAction(TestAction::make('approve')->table($review));

    expect($review->fresh()->status)->toBe(ProductFeedback::STATUS_APPROVED);
    $this->get($product->url)->assertSee($review->content)->assertSee('"@type":"Review"', false);

    Livewire::test(ManageProductFeedback::class)
        ->set('activeTab', 'all')
        ->callAction(TestAction::make('edit')->table($review), data: [
            'display_name' => 'Ana Teste',
            'rating' => 4,
            'content' => 'Texto revisado pelo administrador antes da publicação.',
        ]);

    expect($review->fresh()->rating)->toBe(4)
        ->and($review->fresh()->content)->toBe('Texto revisado pelo administrador antes da publicação.');

    Livewire::test(ManageProductFeedback::class)
        ->set('activeTab', 'all')
        ->callAction(TestAction::make('reject')->table($review));

    $this->get($product->url)->assertDontSee($review->fresh()->content);

    Livewire::test(ManageProductFeedback::class)
        ->set('activeTab', 'all')
        ->callAction(TestAction::make('delete')->table($review));

    expect(ProductFeedback::count())->toBe(0);
});

it('keeps ordinary admins out of feedback moderation', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $this->actingAs($admin)->get('/admin/avaliacoes')->assertForbidden();
    $this->actingAs($admin)->get('/admin/sugestoes')->assertForbidden();
});

it('lets the superadmin edit and delete private suggestions without publishing them', function () {
    $user = makeBuyer();
    $product = makeProduct(['type' => ProductType::Spreadsheet]);
    $suggestion = ProductFeedback::create([
        'product_id' => $product->id,
        'user_id' => $user->id,
        'type' => ProductFeedback::TYPE_SUGGESTION,
        'status' => ProductFeedback::STATUS_PENDING,
        'display_name' => $user->name,
        'subject' => 'Nova tabela',
        'content' => 'Seria útil uma tabela adicional para conferir os resultados.',
    ]);

    $this->actingAs(User::factory()->create(['role' => User::ROLE_SUPERADMIN]));

    $this->get('/admin/sugestoes')->assertOk();
    expect($suggestion->fresh()->status)->toBe(ProductFeedback::STATUS_PENDING);
    $this->get($product->url)->assertDontSee($suggestion->content);

    Livewire::test(ManageProductSuggestions::class)
        ->callAction(TestAction::make('edit')->table($suggestion), data: [
            'display_name' => $user->name,
            'subject' => 'Mais tabelas',
            'content' => 'Uma tabela de conferência por etapa ajudaria bastante.',
        ]);

    expect($suggestion->fresh()->subject)->toBe('Mais tabelas');

    Livewire::test(ManageProductSuggestions::class)
        ->callAction(TestAction::make('delete')->table($suggestion));

    expect(ProductFeedback::count())->toBe(0);
});
