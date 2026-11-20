<?php

namespace Database\Seeders;

use App\Enums\ServiceArea;
use App\Models\Service;
use App\Services\ImageService;
use App\Support\ShowcaseMenu;
use Illuminate\Database\Seeder;

/**
 * Initial services of the showcase, with sales copy written for SEO.
 *
 * Creates missing services and refreshes only fields that still match their
 * original seed copy, preserving edits made in the admin.
 *
 * Covers live in database/seeders/assets/services/{slug}.jpg and go through
 * ImageService like any upload (webp + thumbnail).
 */
class ServiceSeeder extends Seeder
{
    /** Original seed values, fingerprinted per field so admin edits survive a refresh. */
    private const ORIGINAL_COPY_HASHES = [
        'projeto-eletrico' => [
            'headline' => '8c73f0fce83426dfb2677e1e747220bada4de9af8b2c7f48cb2ef5eb133947fd',
            'summary' => '0c52d8221cc70bf66b343ef3afa25a31e7d436d9dc61659199490497a1717692',
            'seo_title' => 'b13c8bedc4c2581a5d35d488b39447c0f2d49314f9c512b8c27b880996334548',
            'seo_description' => 'b625a3abd5f2a65ddfe0cf68cabb02c3ea95aa50c0bf9258ee8d1d167fc3fd04',
            'body' => '7330fa583c3f6947580b807d00732daaf35ab8694c1db44a6e013988970c43d7',
            'deliverables' => '7f00705e753f08f8713cf220d44286e8c0238577204205d61c2302c42e0aca32',
            'faq' => '677e61fbc20b7aef37d9c852d8b1392d1c66c8e65ce995dd9b697b755f4f8c5f',
        ],
        'projeto-arquitetonico' => [
            'headline' => '0b40e901f46048bd4e25c652c8a71106ff9eba39733ff90c64791301963a7b80',
            'summary' => '932c048424879f1901884c060454e4d9659ccd78916ebfc96d52f89d96e9b12c',
            'seo_title' => 'f1f3462a513560d051ab4340910a1445ff40342e1fdad5e1d6ba1162ac9531fa',
            'seo_description' => 'f39c4a59664d4660d14a57c5759144c88e66ba34e5ad9166435a9a570a2e71fa',
            'body' => 'be9f89c2ea4412c34eec8709fe97ee832be9d445e6361a176654f0173608cb3b',
            'deliverables' => '146b5c0a23ffdff263505f31f8ce10c909743c5ae8596dba979addf928a14cbf',
            'faq' => 'f705abd943ad0b042a34e6cf1f89ae426ea4bcd43a463657d5318d13f44c3d69',
        ],
        'projeto-hidrossanitario' => [
            'headline' => '5a394edef4c0732e4e53c01914edf35f1516215da053201bf7b8e8206d2cc4ba',
            'summary' => 'e0519dfca9aeeff6d08232ab608bb2422f77dd2c3fdd53e5ed19f0804fffd6e2',
            'seo_title' => '45e78a1d251b5b561cbd2dac9b96a71cdb4575a903db3fb073e9807da909756c',
            'seo_description' => '16778326f33daef0f2a290cfda063548e245e67ebafb08291c308395711edfe5',
            'body' => '2189acd3922a5ad49e9030243f778cfaa4d6760041c6906faa241b6702dd158d',
            'deliverables' => '3e54d66a2d535d0406c2b6dd9897d2cb0003528c2fdf66cd2cf5d18d07f0bca3',
            'faq' => '01148835e6800867a35876df9d7871c8ad77e9035effbc64ee7c25951914662d',
        ],
        'desenvolvimento-de-sites' => [
            'headline' => '272850bccab692811fd2ad027d76484e907375a926967d54a525e9475f80071c',
            'summary' => '627f055998aa590a83210e9822f917288eb7ce1b6f0ea54626185da546293990',
            'seo_title' => '80fdac66923f2ec57858d96e856454f62fbcea5f5fe1eaea6150405bf39201b1',
            'seo_description' => '5194ca0ec3f08a62351a666df5098c0ed76f59cd190ca72e0097da2cb0714104',
            'body' => '457e1635aca37f252f646deb4cb767fe1dc46414d4530a5c6501b32cd24b413a',
            'deliverables' => 'b7067dd5a476b195d58b2072849f8b446b2d56320450692e663c84a3ba3537f7',
            'faq' => '6deac0319ad5fb655180fd5fc8e3f2a9a4e2a2e75cae52c43e1a56464838f20d',
        ],
        'desenvolvimento-de-aplicativos' => [
            'headline' => '844f2890c47aca5a568ca2ef4556828a4c2a86f2dcf044acb5748a2a0bd08139',
            'summary' => '99f88d53ce2d244d8b1ab9f8ceeed6faa62370094af053a2910ab9405ce29d90',
            'seo_title' => '4681f18cb0b265ddac56f2a21bdf86ceef29fc9b27b6fcb9fb2b439d60e216a1',
            'seo_description' => 'c161089fde603ddea5a21fd8b4753693f0e5fbefe3c48e2e51ba7143cb8ba247',
            'body' => '044b18a6b29a4182c969f51f863af8df4edd92584812c1524f52012641e24fee',
            'deliverables' => 'a37dc367d80f7179ada16d0f5520d0f9a8d35b1326118416cc95e679430a85ce',
            'faq' => '556d3d6f5f06607ec9018eeefdc04ca48abd6b0a632911f8904592e05a90beb2',
        ],
        'desenvolvimento-de-software' => [
            'headline' => 'f16eb4b81a43078284d3cf314230c251c7f62388016129554aa11cc6d342d7e7',
            'summary' => 'da5459e45f2fc724dd2137d7c276a6c0623d2d508cee3d0f5c8e9df2e3b6416a',
            'seo_title' => 'b45f2815d7230c699c6ef191650ccc3aa79fc9d14501c8f71d372c79847e1d13',
            'seo_description' => '35dc621f19bf493b724593a68c564a70d92781743e815245e29dc0d013ce4e29',
            'body' => '6ba2d1f65e3429bc7fddc0150cf7f95808ed6989162a07a0a7da7a21391ae845',
            'deliverables' => '10eed22243cf33278c09b2586778312bd7eed827d65c0395acf332ce06ae762e',
            'faq' => 'da5e131585550dfcce573b5a0e48c497e54e1bf68e83f4fe2225c27bfefefb30',
        ],
    ];

    public function run(): void
    {
        $images = app(ImageService::class);

        foreach (self::services() as $position => $data) {
            $existing = Service::query()->where('slug', $data['slug'])->first();

            if ($existing) {
                if ($existing->slug === 'projeto-estrutural') {
                    $this->refreshStructuralService($existing, $data);
                } else {
                    $this->refreshSeededService($existing, $data);
                }

                continue;
            }

            $service = Service::create([
                ...$data,
                'sort_order' => $position + 1,
                'is_active' => true,
                'is_featured' => true,
            ]);

            $cover = database_path('seeders/assets/services/'.$data['slug'].'.jpg');

            if (is_file($cover)) {
                $service->update([
                    'cover_path' => $images->storeWithSlug(
                        slug: $service->slug,
                        file: $cover,
                        directory: 'services',
                        disk: 'public',
                    ),
                ]);
            }
        }

        // Model events may be muted when called from another seeder.
        ShowcaseMenu::forget();
    }

    /** @param array<string, mixed> $data */
    private function refreshSeededService(Service $service, array $data): void
    {
        $changes = [];

        foreach (self::ORIGINAL_COPY_HASHES[$service->slug] ?? [] as $field => $originalHash) {
            $value = $service->getAttribute($field);

            if ($field === 'faq') {
                $value = array_map(
                    fn (array $item): array => ['question' => $item['question'] ?? null, 'answer' => $item['answer'] ?? null],
                    $value ?? [],
                );
            }

            $fingerprint = is_array($value)
                ? hash('sha256', json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                : hash('sha256', (string) $value);

            if ($fingerprint === $originalHash) {
                $changes[$field] = $data[$field];
            }
        }

        if ($changes !== []) {
            $service->update($changes);
        }
    }

    /** @param array<string, mixed> $data */
    private function refreshStructuralService(Service $service, array $data): void
    {
        $originalText = [
            'headline' => 'Estrutura segura e econômica, calculada e detalhada para a sua obra sair do papel sem improviso.',
            'summary' => 'Cálculo e detalhamento de estruturas em concreto armado para casas, sobrados, edifícios e comércios, conforme a NBR 6118, com ART e projeto pronto para executar.',
            'seo_title' => 'Projeto Estrutural Residencial e Comercial com ART',
            'seo_description' => 'Projeto estrutural em concreto armado conforme a NBR 6118, com ART e detalhamento completo. Estrutura segura e sem desperdício. Atendimento em todo o Brasil.',
        ];
        $previousHeadline = 'Antes de concretar, saiba onde cada fundação, pilar, viga e laje precisa estar — e como executar a estrutura da sua obra.';

        $changes = [];

        foreach ($originalText as $field => $originalValue) {
            if ($service->getAttribute($field) === $originalValue || ($field === 'headline' && $service->headline === $previousHeadline)) {
                $changes[$field] = $data[$field];
            }
        }

        if ($service->body && hash('sha256', $service->body) === '0d036fecbe01061d633a18f657e3f16a27d5a3ee5d8a332665b33e54848a97be') {
            $changes['body'] = $data['body'];
        }

        $originalFaq = [
            ['question' => 'Quanto custa um projeto estrutural?', 'answer' => 'O valor depende da área construída, do número de pavimentos e da complexidade da estrutura. Envie as medidas ou o projeto arquitetônico e receba uma proposta sem compromisso.'],
            ['question' => 'Preciso ter o projeto arquitetônico antes?', 'answer' => 'Sim, a estrutura é lançada sobre o projeto arquitetônico. Se você ainda não tem, também faço o arquitetônico, já pensando na estrutura desde o início.'],
            ['question' => 'O projeto estrutural ajuda a economizar na obra?', 'answer' => 'Sim. Com as peças dimensionadas na medida certa você compra só o concreto e o aço necessários, e o quantitativo do projeto ajuda a orçar a obra com precisão.'],
            ['question' => 'O projeto vem com ART?', 'answer' => 'Sim. Todo projeto é entregue com ART (Anotação de Responsabilidade Técnica) registrada no CREA, assinada pelo engenheiro civil responsável.'],
            ['question' => 'Você atende obras em outras cidades e estados?', 'answer' => 'Sim, o atendimento é online. Para obras fora de Mato Grosso, as exigências do CREA do estado da obra são verificadas e informadas na proposta.'],
        ];

        $currentFaq = array_map(
            fn (array $item): array => ['question' => $item['question'] ?? null, 'answer' => $item['answer'] ?? null],
            $service->faq ?? [],
        );

        if ($currentFaq === $originalFaq) {
            $changes['faq'] = $data['faq'];
        }

        if ($changes !== []) {
            $service->update($changes);
        }
    }

    /**
     * Slugs of the default services that are not in the database.
     *
     * @return array<int, string>
     */
    public static function missing(): array
    {
        $slugs = array_column(self::services(), 'slug');

        return array_values(array_diff($slugs, Service::query()->whereIn('slug', $slugs)->pluck('slug')->all()));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function services(): array
    {
        return [
            ...self::engineering(),
            ...self::technology(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function engineering(): array
    {
        return [
            [
                'area' => ServiceArea::Engineering,
                'name' => 'Projeto Estrutural',
                'slug' => 'projeto-estrutural',
                'icon' => 'building-office-2',
                'headline' => 'Antes de concretar, saiba onde cada fundação, pilar, viga e laje precisa estar e como executar a estrutura da sua obra.',
                'summary' => 'Projeto estrutural em concreto armado para casas, sobrados e edificações comerciais, com detalhamento, quantitativos e ART. Atendimento online em todo o Brasil.',
                'seo_title' => 'Projeto Estrutural em Concreto Armado com ART | Ronaldo Aires',
                'seo_description' => 'Projeto estrutural para casas, sobrados e obras comerciais: fundações, pilares, vigas e lajes detalhados, quantitativos e ART. Peça um orçamento.',
                'body' => <<<'HTML'
<h2>A estrutura não deveria ser decidida no canteiro</h2>
<p>O projeto arquitetônico mostra os ambientes. Antes de começar a fundação, ainda é preciso definir como as cargas vão chegar ao solo, onde ficam os pilares e como vigas e lajes trabalham juntas. Sem essas respostas, compras e decisões de execução ficam sujeitas a mudanças durante a obra.</p>
<h2>O que o projeto estrutural resolve</h2>
<p>Eu dimensiono e detalho <strong>fundações, pilares, vigas e lajes</strong> para a sua edificação. O projeto mostra dimensões, posição das peças e armaduras, além de reunir desenhos e quantitativos para orientar a equipe e planejar materiais. A entrega inclui a ART do responsável técnico.</p>
<ul>
<li><strong>Decisões antes da concretagem:</strong> a equipe consulta as plantas para entender onde executar cada elemento.</li>
<li><strong>Compras com referência:</strong> os quantitativos ajudam a orçar concreto, aço e formas antes de pedir material.</li>
<li><strong>Menos conflitos entre projetos:</strong> a estrutura é compatibilizada com a arquitetura e as demais disciplinas quando elas estão disponíveis.</li>
<li><strong>Responsabilidade técnica identificada:</strong> a ART registra o profissional responsável pelo projeto.</li>
</ul>
<h2>Em que momento vale conversar?</h2>
<p>Se você vai construir uma casa, um sobrado ou uma edificação comercial, o melhor momento é <strong>antes de iniciar as fundações</strong>. Para ampliações e novos pavimentos sobre uma construção existente, primeiro é necessário avaliar as informações e as condições da estrutura atual para definir o escopo do trabalho.</p>
<h2>Da arquitetura ao detalhamento</h2>
<p>Começo pelo projeto arquitetônico e pelas informações do terreno. A partir disso, defino o sistema estrutural, faço o dimensionamento e preparo as plantas e os detalhes de execução. A modelagem em <strong>BIM</strong> ajuda a conferir interferências com arquitetura, instalações elétricas e hidrossanitárias antes da obra.</p>
HTML,
                'deliverables' => [
                    'Planta de locação e cargas das fundações',
                    'Plantas de formas de todos os pavimentos',
                    'Detalhamento de armaduras de fundações, pilares, vigas e lajes',
                    'Quantitativo de concreto, aço e formas',
                    'Memorial descritivo',
                    'Arquivos em PDF e DWG',
                    'ART registrada no CREA',
                ],
                'faq' => [
                    ['question' => 'Quanto custa um projeto estrutural?', 'answer' => 'O valor depende da área, do número de pavimentos, das informações disponíveis e da complexidade da estrutura. Envie o projeto arquitetônico ou descreva a obra para receber uma proposta com escopo, prazo e valor.'],
                    ['question' => 'Preciso ter o projeto arquitetônico antes?', 'answer' => 'O projeto arquitetônico é a base para definir e compatibilizar a estrutura. Se você ainda não o tem, pode me contar o que pretende construir; também desenvolvo o projeto arquitetônico.'],
                    ['question' => 'O projeto estrutural ajuda a planejar os gastos da obra?', 'answer' => 'Sim. O detalhamento e os quantitativos dão uma referência para orçar concreto, aço e formas. O consumo real e o custo final dependem da execução, dos preços e das condições da obra.'],
                    ['question' => 'O projeto vem com ART?', 'answer' => 'Sim. Todo projeto é entregue com ART (Anotação de Responsabilidade Técnica) registrada no CREA, assinada pelo engenheiro civil responsável.'],
                    ['question' => 'Você atende obras em outras cidades e estados?', 'answer' => 'Sim, o atendimento é online. Para obras fora de Mato Grosso, as exigências do CREA do estado da obra são verificadas e informadas na proposta.'],
                    ['question' => 'Quero ampliar ou construir outro pavimento. Você pode projetar?', 'answer' => 'Posso avaliar a solicitação. Antes de projetar a ampliação, é necessário verificar as informações e as condições da estrutura existente. Essa etapa e os serviços necessários são definidos na proposta.'],
                ],
            ],
            [
                'area' => ServiceArea::Engineering,
                'name' => 'Projeto Elétrico',
                'slug' => 'projeto-eletrico',
                'icon' => 'bolt',
                'headline' => 'Defina tomadas, iluminação, circuitos e proteções antes da instalação começar.',
                'summary' => 'Projeto elétrico para casas e espaços comerciais, com pontos, circuitos, quadro de cargas, diagramas e ART. Atendimento online.',
                'seo_title' => 'Projeto Elétrico Residencial e Comercial com ART',
                'seo_description' => 'Projeto elétrico com tomadas, iluminação, circuitos, quadro de cargas, diagramas e ART para orientar a execução. Solicite uma proposta.',
                'body' => <<<'HTML'
<h2>A instalação começa antes de passar o primeiro cabo</h2>
<p>Sem uma planta elétrica, a posição das tomadas e a divisão dos circuitos acabam sendo decididas durante a obra. Quando os equipamentos entram em cena, pode ser preciso rever pontos, fiação e quadro. O projeto permite discutir essas escolhas antes de fechar paredes e forros.</p>
<h2>O que o projeto elétrico define</h2>
<p>Planejo <strong>tomadas, interruptores, iluminação, circuitos, cabos e proteções</strong> de acordo com os ambientes e as cargas informadas. As plantas, o quadro de cargas e os diagramas orientam a equipe na execução. A entrega inclui ART do responsável técnico.</p>
<ul>
<li><strong>Uso dos ambientes:</strong> os pontos são posicionados considerando móveis, equipamentos e circulação.</li>
<li><strong>Cargas previstas:</strong> chuveiro, ar-condicionado e outros equipamentos informados entram no dimensionamento.</li>
<li><strong>Equipe orientada:</strong> plantas, circuitos e diagramas mostram o que instalar e onde instalar.</li>
<li><strong>Integração com a obra:</strong> quando os demais projetos estão disponíveis, verifico interferências antes da execução.</li>
</ul>
<h2>Construção, reforma ou aumento de carga</h2>
<p>O escopo pode atender uma obra nova ou a adequação de uma instalação existente. Em reformas, primeiro avalio as informações disponíveis e os equipamentos que você pretende usar. Se houver exigências da concessionária, a documentação necessária é definida na proposta.</p>
<h2>Como o trabalho começa</h2>
<p>Envie a planta, informe os equipamentos previstos e conte se já existe instalação no local. Com esses dados, preparo uma proposta com escopo, prazo e valor. O projeto segue as normas aplicáveis e pode ser compatibilizado com arquitetura, estrutura e instalações hidrossanitárias.</p>
HTML,
                'deliverables' => [
                    'Planta baixa com tomadas, interruptores e pontos de iluminação',
                    'Divisão e dimensionamento dos circuitos',
                    'Quadro de cargas e cálculo da demanda',
                    'Diagramas unifilares dos quadros de distribuição',
                    'Padrão de entrada quando necessário no escopo',
                    'Lista de materiais',
                    'Memorial descritivo',
                    'ART registrada no CREA',
                ],
                'faq' => [
                    ['question' => 'Preciso ter a planta arquitetônica?', 'answer' => 'Ela ajuda a posicionar os pontos e compatibilizar a instalação. Se ainda não a tem, envie as medidas e descreva a obra para avaliarmos o que falta antes de começar.'],
                    ['question' => 'Posso prever ar-condicionado ou energia solar?', 'answer' => 'Sim. Informe os equipamentos e a capacidade prevista. O que será incluído no projeto e as exigências de cada sistema ficam definidos na proposta.'],
                    ['question' => 'O projeto serve para a concessionária?', 'answer' => 'A documentação para ligação ou aumento de carga depende da concessionária e do tipo de solicitação. Verifico as exigências aplicáveis e incluo esse serviço no escopo quando necessário.'],
                    ['question' => 'Você atende uma reforma elétrica?', 'answer' => 'Sim. Primeiro avalio a instalação existente e as mudanças desejadas. O projeto de adequação pode incluir novos pontos, circuitos e revisão de cargas.'],
                    ['question' => 'Quanto custa o projeto elétrico?', 'answer' => 'O valor depende da área, da quantidade de pontos, das cargas e do estado da instalação existente. Envie a planta ou as medidas para receber uma proposta com escopo e prazo.'],
                ],
            ],
            [
                'area' => ServiceArea::Engineering,
                'name' => 'Projeto Arquitetônico',
                'slug' => 'projeto-arquitetonico',
                'icon' => 'home-modern',
                'headline' => 'Veja os ambientes e resolva as escolhas da obra no projeto, antes de construir.',
                'summary' => 'Projeto arquitetônico para casas, sobrados e espaços comerciais, com plantas, cortes, fachadas, imagens 3D e documentação legal conforme o escopo.',
                'seo_title' => 'Projeto Arquitetônico para Casas e Sobrados em 3D',
                'seo_description' => 'Projeto arquitetônico com plantas, cortes, fachadas e imagens 3D para casas, sobrados e comércios. Conte sua ideia e solicite uma proposta.',
                'body' => <<<'HTML'
<h2>Uma planta precisa funcionar antes de parecer bonita</h2>
<p>Construir sem resolver circulação, iluminação e medidas no papel deixa decisões importantes para o canteiro. O projeto arquitetônico organiza o uso dos ambientes e permite avaliar alternativas antes que uma parede esteja pronta.</p>
<h2>Da sua ideia a uma base clara para a obra</h2>
<p>Desenvolvo <strong>plantas, cortes, fachadas e imagens 3D</strong> a partir do terreno, da rotina de quem vai usar o espaço e das exigências locais. O estudo preliminar abre espaço para ajustes antes do detalhamento.</p>
<ul>
<li><strong>Entenda o espaço:</strong> veja a distribuição dos ambientes e as dimensões antes da obra.</li>
<li><strong>Compare escolhas:</strong> imagens 3D ajudam a conversar sobre volumes, fachada e materiais.</li>
<li><strong>Planeje os próximos projetos:</strong> a arquitetura serve de base para estrutura, elétrica e hidrossanitário.</li>
<li><strong>Prepare a documentação:</strong> o projeto legal considera as exigências do município; o processo de aprovação depende da prefeitura.</li>
</ul>
<h2>Para construir, reformar ou ampliar</h2>
<p>Atendo casas, sobrados e espaços comerciais. Em reformas e ampliações, o levantamento do que já existe e as condições do imóvel ajudam a definir o escopo. A documentação para prefeitura e eventuais projetos complementares são combinados na proposta.</p>
<h2>Como começamos</h2>
<p>Conte o que pretende construir, como quer usar os ambientes e o que já sabe sobre o terreno. Com essas informações, preparo uma proposta com etapas, entregáveis, revisões, prazo e valor. O trabalho pode ser modelado em <strong>BIM</strong> para apoiar a compatibilização com as demais disciplinas.</p>
HTML,
                'deliverables' => [
                    'Estudo preliminar com a distribuição dos ambientes',
                    'Plantas baixas cotadas',
                    'Planta de situação, locação e cobertura',
                    'Cortes e fachadas',
                    'Imagens 3D do projeto',
                    'Quadro de áreas',
                    'Projeto legal conforme exigências municipais e escopo',
                    'ART registrada no CREA',
                ],
                'faq' => [
                    ['question' => 'Posso pedir alterações?', 'answer' => 'Sim. O estudo preliminar é apresentado para discussão antes do detalhamento. A quantidade de revisões e o que cada etapa inclui ficam definidos na proposta.'],
                    ['question' => 'O projeto garante aprovação na prefeitura?', 'answer' => 'O projeto legal é preparado conforme as exigências municipais conhecidas para o imóvel. A análise e a decisão de aprovação são da prefeitura; o acompanhamento do processo pode ser incluído no escopo.'],
                    ['question' => 'Você projeta reformas e ampliações?', 'answer' => 'Sim. Para definir a intervenção, preciso conhecer o imóvel existente e as informações disponíveis. Levantamento, análise da estrutura e projetos complementares são avaliados conforme o caso.'],
                    ['question' => 'Posso contratar os projetos complementares?', 'answer' => 'Sim. Estrutural, elétrico e hidrossanitário podem integrar a proposta. Quando desenvolvidos em conjunto, os projetos podem ser compatibilizados antes da execução.'],
                    ['question' => 'Quanto custa o projeto arquitetônico?', 'answer' => 'O valor depende da área, da complexidade, das imagens, das revisões e da documentação necessária. Envie as informações do terreno e sua ideia para receber uma proposta.'],
                ],
            ],
            [
                'area' => ServiceArea::Engineering,
                'name' => 'Projeto Hidrossanitário',
                'slug' => 'projeto-hidrossanitario',
                'icon' => 'beaker',
                'headline' => 'Saiba por onde passam água, esgoto e águas da chuva antes de instalar e fechar as paredes.',
                'summary' => 'Projeto hidrossanitário de água fria e quente, esgoto e águas pluviais, com dimensionamento, plantas, detalhes e ART.',
                'seo_title' => 'Projeto Hidrossanitário: Água, Esgoto e Águas Pluviais',
                'seo_description' => 'Projeto hidrossanitário com ART: água fria e quente, esgoto e águas pluviais planejados para orientar a execução. Solicite uma proposta.',
                'body' => <<<'HTML'
<h2>Tubulações não deveriam ser decididas depois da alvenaria</h2>
<p>Quando os caminhos da água e do esgoto são improvisados na execução, pontos podem disputar espaço com vigas, paredes e equipamentos. O projeto coloca essas decisões na planta, antes de comprar e instalar materiais.</p>
<h2>O que o projeto hidrossanitário define</h2>
<p>Planejo <strong>água fria e quente, esgoto sanitário e águas pluviais</strong> conforme as necessidades da edificação. O dimensionamento e os desenhos indicam traçados, diâmetros, reservatório, caixas, calhas e condutores previstos no escopo.</p>
<ul>
<li><strong>Uso previsto:</strong> os pontos são definidos com base em banheiros, cozinha, área de serviço e aquecimento.</li>
<li><strong>Esgoto planejado:</strong> traçados, caixas e ventilação são detalhados para a execução.</li>
<li><strong>Cobertura considerada:</strong> calhas e condutores de chuva são dimensionados para o projeto.</li>
<li><strong>Consulta futura:</strong> as plantas registram por onde passam as instalações projetadas.</li>
</ul>
<h2>Obra nova ou reforma</h2>
<p>Atendo casas, sobrados e espaços comerciais. Na reforma, preciso conhecer a instalação existente antes de definir o que pode ser aproveitado ou alterado. Em locais sem rede pública de esgoto, o tratamento individual deve ser informado para avaliação do escopo.</p>
<h2>Como começamos</h2>
<p>Envie a planta, informe os pontos de água e o tipo de aquecimento pretendido. Verifico as informações necessárias, as normas aplicáveis e as interfaces com arquitetura e estrutura. A proposta define entregáveis, prazo e valor.</p>
HTML,
                'deliverables' => [
                    'Plantas de água fria e água quente',
                    'Isométricos das instalações de água',
                    'Plantas de esgoto sanitário com ventilação',
                    'Plantas de águas pluviais com calhas e condutores',
                    'Dimensionamento do reservatório',
                    'Detalhes de caixas e ligações',
                    'Lista de materiais',
                    'ART registrada no CREA',
                ],
                'faq' => [
                    ['question' => 'Água quente está incluída?', 'answer' => 'Pode ser incluída. Informe o sistema de aquecimento previsto para que os pontos e o dimensionamento entrem no escopo da proposta.'],
                    ['question' => 'O projeto resolve a baixa pressão de uma instalação existente?', 'answer' => 'Primeiro é preciso avaliar a instalação, o reservatório e os equipamentos existentes. A proposta pode incluir diagnóstico e projeto de adequação; não é possível prometer uma solução sem essa análise.'],
                    ['question' => 'Você dimensiona o reservatório?', 'answer' => 'Sim, conforme o consumo previsto e as informações da edificação. A verificação da estrutura que o apoia é um serviço diferente e pode ser avaliada junto com o projeto estrutural.'],
                    ['question' => 'E se não houver rede pública de esgoto?', 'answer' => 'Informe essa condição no pedido. O tratamento individual e suas exigências locais podem ser avaliados para inclusão na proposta.'],
                    ['question' => 'Quanto custa o projeto hidrossanitário?', 'answer' => 'Depende da área, dos pontos de consumo, da cobertura e das condições da instalação existente, se houver. Envie a planta para receber uma proposta com escopo e prazo.'],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function technology(): array
    {
        return [
            [
                'area' => ServiceArea::Technology,
                'name' => 'Desenvolvimento de Sites',
                'slug' => 'desenvolvimento-de-sites',
                'icon' => 'globe-alt',
                'headline' => 'Apresente seu negócio com clareza e ajude o visitante a encontrar o caminho para falar com você.',
                'summary' => 'Criação de sites profissionais sob medida, responsivos e preparados para buscas, com páginas e ferramentas definidas para o seu negócio.',
                'seo_title' => 'Desenvolvimento de Sites Profissionais sob Medida',
                'seo_description' => 'Criação de sites profissionais responsivos, com conteúdo claro, SEO técnico e opções de gestão. Conte o que sua empresa precisa e peça uma proposta.',
                'body' => <<<'HTML'
<h2>O visitante precisa entender sua oferta em poucos segundos</h2>
<p>Quando serviços, preços e formas de contato estão espalhados ou difíceis de encontrar, a pessoa pode sair antes de pedir orçamento. Um site próprio organiza essas informações e mostra o próximo passo em cada página.</p>
<h2>Um site construído em torno do seu objetivo</h2>
<p>Defino com você o público, as páginas, o conteúdo e a ação principal, como <strong>pedir orçamento, agendar ou comprar</strong>. A estrutura, o layout e os formulários são desenvolvidos para esse caminho, com uso confortável no celular.</p>
<ul>
<li><strong>Mensagem organizada:</strong> o visitante encontra o que você oferece e como entrar em contato.</li>
<li><strong>Leitura no celular:</strong> páginas responsivas e controles fáceis de usar em telas pequenas.</li>
<li><strong>Base técnica para buscas:</strong> títulos, URLs, sitemap e marcação quando fizerem sentido para o projeto.</li>
<li><strong>Atualização de conteúdo:</strong> painel administrativo quando o negócio precisa editar páginas, produtos ou serviços.</li>
</ul>
<h2>Do site institucional à loja virtual</h2>
<p>O trabalho pode incluir landing page, catálogo, loja ou área de membros. Integrações, painel, pagamentos, domínio e hospedagem são definidos na proposta. SEO técnico ajuda os mecanismos de busca a entender o site, mas a posição e o volume de visitas dependem também de conteúdo e concorrência.</p>
<h2>Como começamos</h2>
<p>Conte o que sua empresa faz, quem quer alcançar e qual ação espera do visitante. Com essas informações, organizo o escopo, as páginas e as funcionalidades e envio uma proposta com prazo e valor.</p>
HTML,
                'deliverables' => [
                    'Layout exclusivo, adaptado a celular, tablet e computador',
                    'Painel administrativo para os conteúdos definidos no escopo',
                    'SEO técnico: sitemap, dados estruturados e URLs amigáveis',
                    'Formulários de contato e botão de WhatsApp',
                    'Integrações definidas na proposta',
                    'Orientação sobre domínio, hospedagem e publicação',
                    'Orientação de uso do painel, quando incluído',
                    'Condições de suporte definidas na proposta',
                ],
                'faq' => [
                    ['question' => 'Quanto tempo leva para o site ficar pronto?', 'answer' => 'O prazo depende do número de páginas, das integrações e do material disponível. A proposta informa as etapas e o prazo depois que o escopo for definido.'],
                    ['question' => 'Poderei atualizar o conteúdo?', 'answer' => 'Sim, quando um painel administrativo fizer parte do projeto. Definimos quais textos, imagens, produtos ou serviços você poderá editar e incluo uma orientação de uso na entrega.'],
                    ['question' => 'O site vai ficar em primeiro lugar no Google?', 'answer' => 'Nenhuma posição pode ser garantida. O projeto pode incluir SEO técnico e preparação para indexação; resultados dependem também de conteúdo, concorrência e tempo.'],
                    ['question' => 'Quem paga domínio e hospedagem?', 'answer' => 'Esses serviços têm custos próprios e podem ficar em contas no seu nome. Ajudo a escolher e configurar a solução adequada; valores recorrentes são informados antes da contratação.'],
                    ['question' => 'Você desenvolve loja virtual?', 'answer' => 'Sim. Catálogo, carrinho, pagamentos e gestão de pedidos podem integrar o projeto. Os recursos e as taxas dos provedores são definidos na proposta.'],
                ],
            ],
            [
                'area' => ServiceArea::Technology,
                'name' => 'Desenvolvimento de Aplicativos',
                'slug' => 'desenvolvimento-de-aplicativos',
                'icon' => 'device-phone-mobile',
                'headline' => 'Transforme uma tarefa importante do seu negócio em um aplicativo simples de usar.',
                'summary' => 'Desenvolvimento de aplicativos para Android e iPhone, com protótipo, funcionalidades e integrações definidas conforme a necessidade do negócio.',
                'seo_title' => 'Desenvolvimento de Aplicativos Android e iOS sob Medida',
                'seo_description' => 'Desenvolvimento de aplicativos Android e iPhone sob medida, do protótipo à publicação prevista no escopo. Descreva sua ideia e peça uma proposta.',
                'body' => <<<'HTML'
<h2>O aplicativo começa pela tarefa que precisa funcionar</h2>
<p>Pedidos, agendamentos ou acompanhamento de serviços podem exigir muitas telas e integrações. Antes de programar, identifico quem vai usar o app e qual fluxo precisa ser simples do início ao fim. Isso ajuda a definir o que entra na primeira versão.</p>
<h2>Da ideia ao protótipo</h2>
<p>Desenho as telas e o caminho principal para validação antes do desenvolvimento. Depois, preparo o aplicativo e as integrações previstas no escopo, como <strong>login, notificações, pagamentos ou conexão com um sistema existente</strong>.</p>
<ul>
<li><strong>Fluxo validado:</strong> protótipo para discutir a experiência antes da programação.</li>
<li><strong>Plataformas definidas:</strong> Android e iPhone avaliados conforme os recursos necessários.</li>
<li><strong>Gestão prevista:</strong> painel web pode fazer parte da solução quando houver conteúdo ou usuários para administrar.</li>
<li><strong>Publicação planejada:</strong> preparo a submissão às lojas quando ela estiver no escopo; a aprovação depende de cada loja.</li>
</ul>
<h2>Um app só faz sentido quando há motivo para voltar</h2>
<p>Para alguns negócios, um site responsivo resolve melhor. Para outros, câmera, notificações, uso frequente ou acesso a recursos do celular justificam o aplicativo. Essa escolha é discutida antes da proposta, sem pressupor funções que o usuário não precisa.</p>
<h2>Como começamos</h2>
<p>Descreva os usuários, a tarefa principal e os sistemas que o app precisa acessar. Com isso, preparo uma proposta com versão inicial, etapas, integrações, publicação e custos externos quando aplicáveis.</p>
HTML,
                'deliverables' => [
                    'Protótipo das telas para aprovação',
                    'Aplicativo para Android e iOS',
                    'API e banco de dados quando necessários',
                    'Painel administrativo quando previsto no escopo',
                    'Notificações quando previstas no escopo',
                    'Preparação para publicação conforme a proposta',
                    'Código-fonte do projeto',
                    'Condições de suporte definidas na proposta',
                ],
                'faq' => [
                    ['question' => 'Preciso de um aplicativo ou um site já resolve?', 'answer' => 'Depende da tarefa. Se o uso acontece no navegador e não depende de recursos do celular, um site pode ser suficiente. Avalio isso com você antes de definir o escopo.'],
                    ['question' => 'Quanto custa desenvolver um aplicativo?', 'answer' => 'O valor depende do fluxo principal, das telas, das integrações e da publicação. Descreva a ideia para receber uma proposta com etapas e prazo.'],
                    ['question' => 'A publicação nas lojas está incluída?', 'answer' => 'Pode estar. A proposta informa se preparo a submissão, quais contas e taxas são necessárias e quem será responsável por elas. A aprovação final depende das lojas.'],
                    ['question' => 'O app pode usar meu sistema atual?', 'answer' => 'Se o sistema oferecer API ou outra forma de integração, avalio a conexão. Caso contrário, a necessidade de adaptar o sistema entra na proposta.'],
                    ['question' => 'O aplicativo pode funcionar sem internet?', 'answer' => 'Algumas funções podem guardar dados no aparelho e sincronizar depois. Isso depende do tipo de informação e é definido no escopo técnico.'],
                ],
            ],
            [
                'area' => ServiceArea::Technology,
                'name' => 'Desenvolvimento de Software',
                'slug' => 'desenvolvimento-de-software',
                'icon' => 'code-bracket',
                'headline' => 'Organize o processo que hoje depende de planilhas, mensagens e trabalho repetido.',
                'summary' => 'Desenvolvimento de sistemas web sob medida para reunir informações, apoiar rotinas e integrar ferramentas, com implantação por etapas.',
                'seo_title' => 'Desenvolvimento de Software sob Medida e Sistemas Web',
                'seo_description' => 'Desenvolvimento de software sob medida para organizar processos, reunir dados e integrar ferramentas. Descreva sua operação e solicite uma proposta.',
                'body' => <<<'HTML'
<h2>Quando a informação se perde entre planilhas e mensagens</h2>
<p>Pedidos duplicados, versões diferentes do mesmo arquivo e tarefas repassadas manualmente dificultam o acompanhamento do trabalho. Antes de criar telas, é preciso entender o processo e onde ele falha.</p>
<h2>Software construído a partir do seu fluxo</h2>
<p>Mapeio as etapas, os usuários e os dados necessários. A primeira entrega prioriza <strong>uma rotina útil para a equipe</strong>, como acompanhar pedidos, orçamentos, obras ou atendimento. Novas funções entram em etapas combinadas.</p>
<ul>
<li><strong>Dados organizados:</strong> registros e históricos em um lugar definido, com acesso conforme o perfil.</li>
<li><strong>Etapas visíveis:</strong> a equipe acompanha o estado de cada trabalho sem depender de repasses dispersos.</li>
<li><strong>Indicadores relevantes:</strong> painéis e relatórios são definidos a partir das decisões que você precisa tomar.</li>
<li><strong>Integrações úteis:</strong> conexão com pagamentos, e-mail ou outros sistemas quando houver viabilidade técnica.</li>
</ul>
<h2>Comece pelo problema mais caro de administrar</h2>
<p>O sistema pode atender orçamentos, pedidos, clientes, obras, estoque ou área do cliente. Não é necessário construir tudo de uma vez: a proposta separa o primeiro fluxo das evoluções futuras e identifica os dados que precisam ser migrados.</p>
<h2>Como começamos</h2>
<p>Mostre como a equipe trabalha hoje, quais planilhas usa e onde há retrabalho. Analiso requisitos, acessos, integrações e cuidados com dados pessoais. A proposta traz o escopo inicial, etapas, prazo, valor e condições de suporte.</p>
HTML,
                'deliverables' => [
                    'Levantamento dos processos e requisitos',
                    'Protótipo das telas para aprovação',
                    'Sistema web com controle de acesso por perfil',
                    'Painéis e relatórios definidos no escopo',
                    'Integrações viáveis definidas na proposta',
                    'Implantação e treinamento da equipe',
                    'Código-fonte e documentação',
                    'Condições de suporte e evolução definidas na proposta',
                ],
                'faq' => [
                    ['question' => 'Quando vale criar um sistema sob medida?', 'answer' => 'Quando ferramentas prontas deixam lacunas importantes no processo. Primeiro avalio o fluxo e as alternativas existentes para definir se desenvolver um sistema é a melhor opção.'],
                    ['question' => 'O código-fonte será entregue?', 'answer' => 'As condições de entrega, uso e manutenção do código-fonte são definidas na proposta e no contrato antes do desenvolvimento.'],
                    ['question' => 'É possível começar com poucas funções?', 'answer' => 'Sim. Podemos priorizar uma primeira rotina utilizável e planejar as demais etapas conforme o uso e o orçamento.'],
                    ['question' => 'O sistema funciona no celular?', 'answer' => 'O acesso pelo navegador do celular pode fazer parte do projeto. As telas e tarefas que precisam funcionar em cada dispositivo são definidas no escopo.'],
                    ['question' => 'Quanto custa um software sob medida?', 'answer' => 'O valor depende dos fluxos, usuários, integrações, dados a migrar e suporte necessário. Depois de entender o processo, envio uma proposta com etapas, prazo e valor.'],
                ],
            ],
        ];
    }
}
