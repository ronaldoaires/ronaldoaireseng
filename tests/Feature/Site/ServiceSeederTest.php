<?php

use App\Enums\ServiceArea;
use App\Models\Service;
use Database\Seeders\ServiceSeeder;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Default services (database/seeders/ServiceSeeder.php)
|--------------------------------------------------------------------------
*/

beforeEach(fn () => Storage::fake('public'));

it('creates the seven default services with covers', function () {
    $this->seed(ServiceSeeder::class);

    expect(Service::count())->toBe(7)
        ->and(Service::where('is_active', true)->count())->toBe(7)
        ->and(ServiceSeeder::missing())->toBe([]);

    Service::all()->each(function (Service $service) {
        expect($service->cover_path)->not->toBeNull();
        Storage::disk('public')->assertExists($service->cover_path);
    });
});

it('keeps the SEO fields within the limits of the admin form', function () {
    foreach (ServiceSeeder::services() as $service) {
        expect(mb_strlen($service['seo_title']))->toBeLessThanOrEqual(70, $service['slug'])
            ->and(mb_strlen($service['seo_description']))->toBeLessThanOrEqual(160, $service['slug'])
            ->and(mb_strlen($service['summary']))->toBeLessThanOrEqual(300, $service['slug'])
            ->and(is_file(base_path('vendor/blade-ui-kit/blade-heroicons/resources/svg/o-'.$service['icon'].'.svg')))->toBeTrue($service['icon']);
    }
});

it('never overwrites a service edited in the admin', function () {
    $this->seed(ServiceSeeder::class);

    Service::where('slug', 'projeto-estrutural')->update(['headline' => 'Texto editado no painel']);
    Service::where('slug', 'projeto-eletrico')->delete();

    expect(ServiceSeeder::missing())->toBe(['projeto-eletrico']);

    $this->seed(ServiceSeeder::class);

    expect(Service::count())->toBe(7)
        ->and(Service::where('slug', 'projeto-estrutural')->value('headline'))->toBe('Texto editado no painel');
});

it('refreshes original structural copy while keeping custom fields', function () {
    $service = Service::create([
        'area' => ServiceArea::Engineering,
        'name' => 'Projeto Estrutural',
        'slug' => 'projeto-estrutural',
        'headline' => 'Estrutura segura e econômica, calculada e detalhada para a sua obra sair do papel sem improviso.',
        'summary' => 'Cálculo e detalhamento de estruturas em concreto armado para casas, sobrados, edifícios e comércios, conforme a NBR 6118, com ART e projeto pronto para executar.',
        'seo_title' => 'Projeto Estrutural Residencial e Comercial com ART',
        'seo_description' => 'Projeto estrutural em concreto armado conforme a NBR 6118, com ART e detalhamento completo. Estrutura segura e sem desperdício. Atendimento em todo o Brasil.',
        'body' => '<p>Texto personalizado no painel.</p>',
        'faq' => [['question' => 'Dúvida personalizada?', 'answer' => 'Resposta personalizada.']],
        'is_active' => true,
    ]);

    $this->seed(ServiceSeeder::class);

    $service->refresh();

    expect($service->headline)->toContain('Antes de concretar')
        ->and($service->summary)->not->toContain('NBR 6118')
        ->and($service->seo_description)->not->toContain('NBR 6118')
        ->and($service->body)->toBe('<p>Texto personalizado no painel.</p>')
        ->and($service->faq)->toBe([['question' => 'Dúvida personalizada?', 'answer' => 'Resposta personalizada.']]);
});

it('replaces the previous structural headline without changing custom copy', function () {
    $service = Service::create([
        'area' => ServiceArea::Engineering,
        'name' => 'Projeto Estrutural',
        'slug' => 'projeto-estrutural',
        'headline' => 'Antes de concretar, saiba onde cada fundação, pilar, viga e laje precisa estar — e como executar a estrutura da sua obra.',
        'summary' => 'Resumo personalizado.',
        'is_active' => true,
    ]);

    $this->seed(ServiceSeeder::class);

    expect($service->fresh()->headline)->toBe('Antes de concretar, saiba onde cada fundação, pilar, viga e laje precisa estar e como executar a estrutura da sua obra.')
        ->and($service->fresh()->summary)->toBe('Resumo personalizado.');
});

it('refreshes untouched service copy while preserving admin edits', function () {
    $service = Service::create([
        'area' => ServiceArea::Technology,
        'name' => 'Desenvolvimento de Sites',
        'slug' => 'desenvolvimento-de-sites',
        'headline' => 'Um site rápido, bonito e feito para aparecer no Google e transformar visitas em clientes.',
        'summary' => 'Resumo escrito no painel.',
        'deliverables' => [
            'Layout exclusivo, adaptado a celular, tablet e computador',
            'Painel administrativo para editar o conteúdo',
            'SEO técnico: sitemap, dados estruturados e URLs amigáveis',
            'Formulários de contato e botão de WhatsApp',
            'Integração com Google Analytics e Search Console',
            'Publicação, domínio e certificado de segurança (HTTPS)',
            'Treinamento para usar o painel',
            'Suporte após a entrega',
        ],
        'is_active' => true,
    ]);

    $this->seed(ServiceSeeder::class);

    $service->refresh();

    expect($service->headline)->toBe('Apresente seu negócio com clareza e ajude o visitante a encontrar o caminho para falar com você.')
        ->and($service->summary)->toBe('Resumo escrito no painel.')
        ->and($service->deliverables)->toContain('Condições de suporte definidas na proposta');
});

it('shows the service specific benefits and quote invitation', function () {
    $this->seed(ServiceSeeder::class);

    $this->get(route('services.show', ['area' => 'tecnologia', 'service' => 'desenvolvimento-de-sites']))
        ->assertOk()
        ->assertSee('Mensagem clara')
        ->assertSee('Seu site deve explicar o que você faz e facilitar o contato')
        ->assertSee('Solicitar orçamento');
});

it('updates the original structural FAQ even when JSON keys are reordered', function () {
    $service = Service::create([
        'area' => ServiceArea::Engineering,
        'name' => 'Projeto Estrutural',
        'slug' => 'projeto-estrutural',
        'summary' => 'Projeto estrutural original.',
        'is_active' => true,
        'faq' => [
            ['question' => 'Quanto custa um projeto estrutural?', 'answer' => 'O valor depende da área construída, do número de pavimentos e da complexidade da estrutura. Envie as medidas ou o projeto arquitetônico e receba uma proposta sem compromisso.'],
            ['question' => 'Preciso ter o projeto arquitetônico antes?', 'answer' => 'Sim, a estrutura é lançada sobre o projeto arquitetônico. Se você ainda não tem, também faço o arquitetônico, já pensando na estrutura desde o início.'],
            ['question' => 'O projeto estrutural ajuda a economizar na obra?', 'answer' => 'Sim. Com as peças dimensionadas na medida certa você compra só o concreto e o aço necessários, e o quantitativo do projeto ajuda a orçar a obra com precisão.'],
            ['question' => 'O projeto vem com ART?', 'answer' => 'Sim. Todo projeto é entregue com ART (Anotação de Responsabilidade Técnica) registrada no CREA, assinada pelo engenheiro civil responsável.'],
            ['question' => 'Você atende obras em outras cidades e estados?', 'answer' => 'Sim, o atendimento é online. Para obras fora de Mato Grosso, as exigências do CREA do estado da obra são verificadas e informadas na proposta.'],
        ],
    ]);

    $this->seed(ServiceSeeder::class);

    expect($service->fresh()->faq)->toHaveCount(6)
        ->and(array_column($service->fresh()->faq, 'question'))->toContain('O projeto estrutural ajuda a planejar os gastos da obra?');
});

it('presents the structural service with the conceptual cover and a clear quote action', function () {
    $this->seed(ServiceSeeder::class);

    $this->get(route('services.show', ['area' => 'engenharia', 'service' => 'projeto-estrutural']))
        ->assertOk()
        ->assertSee('estrutura-esforcos-eberick-sem-fundo.png')
        ->assertSee('Antes de concretar')
        ->assertSee('A estrutura não deveria ser decidida no canteiro')
        ->assertSee('Solicitar orçamento')
        ->assertSee('Sua obra precisa de uma estrutura definida antes da execução')
        ->assertDontSee('NBR 6118');
});

it('renders every seeded service page with its content and FAQ', function () {
    $this->seed(ServiceSeeder::class);

    foreach (Service::all() as $service) {
        $this->get(route('services.show', ['area' => $service->area->slug(), 'service' => $service->slug]))
            ->assertOk()
            ->assertSee($service->seo_title)
            ->assertSee('<h2>', false)
            ->assertSee('"@type":"FAQPage"', false);
    }
});
