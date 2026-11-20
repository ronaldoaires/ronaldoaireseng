<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'concreto-armado-sem-misterio';

        $previewImages = [
            ['path' => 'products/previews/amostra-10.jpg', 'alt' => 'Página do ebook: o caminho das cargas da laje até o solo'],
            ['path' => 'products/previews/amostra-11.jpg', 'alt' => 'Explicação passo a passo do caminho das cargas'],
            ['path' => 'products/previews/amostra-12.jpg', 'alt' => 'Dúvidas frequentes sobre o caminho das cargas'],
            ['path' => 'products/previews/amostra-13.jpg', 'alt' => 'Referência normativa e resumo sobre o caminho das cargas'],
            ['path' => 'products/previews/amostra-19.jpg', 'alt' => 'Página 14 do livro: os cinco esforços estruturais'],
            ['path' => 'products/previews/amostra-26.jpg', 'alt' => 'Página 21 do livro: ordem de mistura na betoneira'],
            ['path' => 'products/previews/amostra-41.jpg', 'alt' => 'Página do ebook: cobrimento da armadura de concreto'],
            ['path' => 'products/previews/amostra-42.jpg', 'alt' => 'Como garantir o cobrimento com espaçadores'],
            ['path' => 'products/previews/amostra-47.jpg', 'alt' => 'Página 42 do livro: armadura negativa entre lajes'],
            ['path' => 'products/previews/amostra-57.jpg', 'alt' => 'Página 52 do livro: altura de uma viga'],
            ['path' => 'products/previews/amostra-72.jpg', 'alt' => 'Página 67 do livro: dúvidas frequentes sobre pilares'],
            ['path' => 'products/previews/amostra-123.jpg', 'alt' => 'Página do ebook: fissuras no concreto armado'],
            ['path' => 'products/previews/amostra-124.jpg', 'alt' => 'Exemplos de fissuras e quando procurar avaliação técnica'],
            ['path' => 'products/previews/amostra-128.jpg', 'alt' => 'Primeira página do capítulo Os 42 erros que mais estragam estruturas'],
            ['path' => 'products/previews/amostra-129.jpg', 'alt' => 'Exemplos dos primeiros erros de projeto e fundação e como evitá-los'],
        ];

        $salesReasons = [
            ['title' => 'Reconheça erros antes da próxima etapa', 'description' => 'Os 42 erros mais comuns estão organizados do projeto ao uso da estrutura. A lista mostra o problema, o cuidado indicado e onde encontrar a explicação no livro ou na norma.'],
            ['title' => 'Normas sem complicação', 'description' => 'As normas técnicas citadas no livro são explicadas de forma simples, direta e visual. Você entende por que lajes, vigas, pilares, fundações e armaduras precisam de cada cuidado.'],
            ['title' => 'Consulte quando surgir a dúvida', 'description' => 'Cada capítulo começa com uma resposta rápida e reúne dúvidas frequentes, erros comuns e referências normativas. O PDF também fica disponível para leitura online e download na sua conta.'],
        ];

        $existing = Product::withTrashed()->where('slug', $slug)->first();
        $directory = database_path('seeders/assets/products');
        $book = $directory.'/'.$slug.'.pdf';
        $preview = $directory.'/'.$slug.'-amostra.pdf';
        $cover = $directory.'/'.$slug.'-capa.png';

        foreach ([$book, $preview, $cover] as $asset) {
            if (! is_file($asset)) {
                throw new RuntimeException("Arquivo do ebook ausente: {$asset}");
            }
        }

        foreach ($previewImages as $image) {
            $source = $directory.'/'.basename($image['path']);

            if (! is_file($source) || ! Storage::disk('public')->put($image['path'], file_get_contents($source))) {
                throw new RuntimeException("Não foi possível preparar a página de amostra: {$source}");
            }
        }

        $previewPath = 'products/previews/concreto-armado-sem-misterio.pdf';
        $bookPath = 'products/concreto-armado-sem-misterio.pdf';
        if (! Storage::disk('public')->put($previewPath, file_get_contents($preview))) {
            throw new RuntimeException('Não foi possível armazenar a prévia do ebook.');
        }

        if ($existing) {
            $this->refreshExistingProduct($existing, $previewImages, $salesReasons);

            return;
        }

        if (! Storage::disk('local')->put($bookPath, file_get_contents($book))) {
            throw new RuntimeException('Não foi possível armazenar o ebook e a prévia.');
        }
        $coverPath = app(ImageService::class)->storeWithSlug($slug, $cover, 'products', 'public');

        $product = Product::create([
            'type' => ProductType::Ebook,
            'title' => 'Concreto Armado sem Mistério',
            'slug' => $slug,
            'subtitle' => 'As dúvidas da obra respondidas pelas normas',
            'summary' => 'As principais dúvidas da obra respondidas com base nas normas técnicas, explicadas de forma simples e direta. Veja também os 42 erros que mais estragam estruturas.',
            'cover_path' => $coverPath,
            'body' => <<<'HTML'
<h2>Quando a dúvida aparece na obra, a resposta precisa ser clara</h2>
<p>O projeto mostra uma viga, mas por que o aço está naquela posição? Quanto de cobrimento é necessário? Uma fissura exige atenção? Essas perguntas surgem entre a planta e o canteiro. Entender o motivo por trás de cada detalhe ajuda a ler o projeto, conversar com a equipe e perceber quando é hora de chamar o responsável técnico.</p>
<p><strong>Concreto Armado sem Mistério</strong> responde às principais dúvidas da obra em uma sequência fácil de acompanhar: da forma como as cargas chegam à fundação até as verificações que importam durante a execução. Cada assunto relaciona o que se vê na obra com a orientação das normas.</p>
<h2>Um jeito de aprender que começa pela pergunta certa</h2>
<p>Em cada capítulo, você encontra uma resposta rápida “Em 10 segundos”, explicações com ilustrações da estrutura e da armadura, dúvidas frequentes, erros comuns, referência normativa, resumo e um pequeno teste. Há indicações para leitores iniciantes, estudantes e profissionais. Assim você pode entrar pelo assunto que precisa entender e depois aprofundar.</p>
<h2>O que você vai encontrar no livro</h2>
<ul>
<li><strong>Entender a estrutura:</strong> concreto armado, caminho das cargas, tipos de carga e esforços.</li>
<li><strong>Conhecer os materiais:</strong> concreto, aço e cobrimento da armadura.</li>
<li><strong>Reconhecer cada elemento:</strong> lajes, vigas, pilares, fundações, escadas e reservatórios.</li>
<li><strong>Levar o conhecimento à obra:</strong> leitura de projeto estrutural, concretagem, fôrmas, escoramento, cura, fissuras e os 42 erros que mais estragam estruturas.</li>
<li><strong>Aprofundar a prática profissional:</strong> pré-dimensionamento, tabelas de consulta, respostas dos testes, glossário e referências.</li>
</ul>
<h2>Escrito por quem trabalha com estruturas</h2>
<p>Ronaldo Aires é engenheiro civil, CREA-MT 62629, com MBA em gestão de obras e pós-graduação em BIM. Sua atuação em projeto estrutural e modelagem BIM orienta a escolha das perguntas e a forma visual de respondê-las.</p>
<p>Este é um material educativo. Ele ajuda a compreender conceitos e a interpretar informações, mas não substitui o projeto estrutural nem a avaliação de um engenheiro habilitado para uma obra específica.</p>
HTML,
            'price' => 59.90,
            'compare_at_price' => null,
            'highlights' => [
                '161 páginas no PDF, com explicações e ilustrações da estrutura',
                '21 capítulos, incluindo os 42 erros que mais estragam estruturas',
                'Normas técnicas explicadas de forma simples, direta e visual',
                'Leitura online e download do PDF na sua biblioteca',
            ],
            'sales_reasons' => $salesReasons,
            'specs' => [
                ['label' => 'Formato', 'value' => 'PDF + leitura online'],
                ['label' => 'Extensão', 'value' => '161 páginas no PDF'],
                ['label' => 'Conteúdo', 'value' => '21 capítulos em 5 partes'],
                ['label' => 'Edição', 'value' => '1ª edição, 2026'],
                ['label' => 'Autor', 'value' => 'Ronaldo Aires · CREA-MT 62629'],
            ],
            'faq' => [
                ['question' => 'Para quem é este ebook?', 'answer' => 'Para quem quer entender a lógica das estruturas de concreto armado: pessoas ligadas à obra, estudantes e profissionais. O livro sinaliza o nível de cada explicação.'],
                ['question' => 'Preciso saber cálculo estrutural para acompanhar?', 'answer' => 'Não. A leitura começa pelos conceitos e pelas perguntas comuns da obra. Há também trechos de aprofundamento para estudantes e profissionais.'],
                ['question' => 'O livro substitui um projeto ou uma avaliação técnica?', 'answer' => 'Não. O conteúdo é educativo e não substitui projeto estrutural, inspeção nem orientação de engenheiro habilitado para uma obra específica.'],
                ['question' => 'Como recebo e leio o material?', 'answer' => 'Após a confirmação do pagamento, o PDF fica disponível para download e leitura online na biblioteca da sua conta.'],
                ['question' => 'Posso ver algumas páginas antes de comprar?', 'answer' => 'Sim. A prévia gratuita nesta página contém páginas selecionadas do próprio ebook, com exemplos de capítulos e dúvidas frequentes da obra.'],
                ['question' => 'Possuirei acesso vitalício?', 'answer' => 'Sim. O ebook permanece na sua biblioteca sem prazo de expiração enquanto sua conta estiver ativa; você pode ler online e baixar o PDF quando precisar. Melhorias e atualizações de normas incorporadas a esta edição são disponibilizadas sem custo adicional. Novas edições podem ser vendidas separadamente.'],
            ],
            'version' => '1ª edição',
            'has_reader' => true,
            'seo_title' => 'Concreto Armado sem Mistério | Ebook de Ronaldo Aires',
            'seo_description' => 'Ebook de concreto armado: normas técnicas explicadas sem complicação, respostas às dúvidas da obra e 42 erros comuns. Leia uma prévia grátis.',
            'is_published' => true,
            'published_at' => now(),
            'is_featured' => true,
            'sort_order' => 1,
            'preview_pdf_path' => $previewPath,
            'preview_images' => $previewImages,
        ]);

        $product->files()->create([
            'label' => 'Concreto Armado sem Mistério · PDF completo',
            'disk' => 'local',
            'path' => $bookPath,
            'original_name' => 'concreto-armado-sem-misterio.pdf',
            'size_bytes' => filesize($book),
            'version' => '1ª edição',
        ]);
    }

    /**
     * Refreshes only copy still matching the original seed, preserving edits made in the admin.
     *
     * @param  array<int, array{path: string, alt: string}>  $previewImages
     * @param  array<int, array{title: string, description: string}>  $salesReasons
     */
    private function refreshExistingProduct(Product $product, array $previewImages, array $salesReasons): void
    {
        $changes = [];
        $oldPreviewPaths = ['products/previews/amostra-10.jpg', 'products/previews/amostra-41.jpg', 'products/previews/amostra-123.jpg'];
        $previousPreviewPaths = ['products/previews/amostra-128.jpg', 'products/previews/amostra-129.jpg', 'products/previews/amostra-10.jpg', 'products/previews/amostra-41.jpg', 'products/previews/amostra-123.jpg'];
        $latestPreviewPaths = array_map(fn (int $page): string => "products/previews/amostra-{$page}.jpg", [128, 129, 1, 3, 4, 5, 10, 11, 12, 13, 41, 42, 123, 124]);
        $orderedPreviewPaths = array_map(fn (int $page): string => "products/previews/amostra-{$page}.jpg", [1, 3, 4, 5, 10, 11, 12, 13, 26, 41, 42, 123, 124, 128, 129]);
        $currentSeedPreviewPaths = array_map(fn (int $page): string => "products/previews/amostra-{$page}.jpg", [5, 10, 11, 12, 13, 26, 41, 42, 47, 57, 72, 123, 124, 128, 129]);
        $currentPreviewPaths = array_column($product->preview_images ?? [], 'path');

        if ($currentPreviewPaths === [] || $currentPreviewPaths === $oldPreviewPaths || $currentPreviewPaths === $previousPreviewPaths || $currentPreviewPaths === $latestPreviewPaths || $currentPreviewPaths === $orderedPreviewPaths || $currentPreviewPaths === $currentSeedPreviewPaths) {
            $changes['preview_images'] = $previewImages;
        }

        if (blank($product->sales_reasons)) {
            $changes['sales_reasons'] = $salesReasons;
        } elseif (($product->sales_reasons[1]['title'] ?? null) === 'Entenda o que está no projeto'
            && ($product->sales_reasons[1]['description'] ?? null) === 'Ilustrações e linguagem direta explicam por que lajes, vigas, pilares, fundações e armaduras trabalham do jeito que trabalham. Assim você consegue fazer perguntas mais precisas ao responsável técnico.') {
            $updatedReasons = $product->sales_reasons;
            $updatedReasons[1] = $salesReasons[1];
            $changes['sales_reasons'] = $updatedReasons;
        }

        if ($product->summary === 'Entenda o caminho das cargas, os materiais, as peças estruturais e as decisões da obra em uma leitura visual, direta e fundamentada nas normas.') {
            $changes['summary'] = 'As principais dúvidas da obra respondidas com base nas normas técnicas, explicadas de forma simples e direta. Veja também os 42 erros que mais estragam estruturas.';
        } elseif ($product->summary === 'Respostas claras às principais dúvidas da obra em concreto armado, com ilustrações e referências às normas. Conheça também os 42 erros que mais estragam estruturas e como evitá-los.') {
            $changes['summary'] = 'As principais dúvidas da obra respondidas com base nas normas técnicas, explicadas de forma simples e direta. Veja também os 42 erros que mais estragam estruturas.';
        }

        if ($product->seo_description === 'Ebook de concreto armado com 161 páginas e 21 capítulos. Entenda projetos, materiais, execução e normas. Veja páginas grátis antes de comprar.') {
            $changes['seo_description'] = 'Ebook de concreto armado: normas técnicas explicadas sem complicação, respostas às dúvidas da obra e 42 erros comuns. Leia uma prévia grátis.';
        } elseif ($product->seo_description === 'Ebook de concreto armado: dúvidas da obra respondidas pelas normas, 21 capítulos e 42 erros comuns explicados. Veja páginas reais antes de comprar.') {
            $changes['seo_description'] = 'Ebook de concreto armado: normas técnicas explicadas sem complicação, respostas às dúvidas da obra e 42 erros comuns. Leia uma prévia grátis.';
        }

        $oldSentence = 'organiza essas respostas em uma sequência fácil de acompanhar';
        $body = $product->body ?? '';

        if (str_contains($body, $oldSentence)) {
            $body = str_replace($oldSentence, 'responde às principais dúvidas da obra em uma sequência fácil de acompanhar', $body);
        }

        $body = str_replace('cura, fissuras e erros recorrentes.', 'cura, fissuras e os 42 erros que mais estragam estruturas.', $body);

        if ($body !== ($product->body ?? '')) {
            $changes['body'] = $body;
        }

        $oldHighlights = [
            '161 páginas no PDF, com explicações e ilustrações da estrutura',
            '21 capítulos: dos conceitos básicos às dúvidas da execução',
            'Leitura online e download do PDF na sua biblioteca',
        ];

        $previousHighlights = [
            $oldHighlights[0],
            '21 capítulos, incluindo os 42 erros que mais estragam estruturas',
            $oldHighlights[2],
        ];

        if ($product->highlights === $oldHighlights || $product->highlights === $previousHighlights) {
            $changes['highlights'] = [
                $oldHighlights[0],
                '21 capítulos, incluindo os 42 erros que mais estragam estruturas',
                'Normas técnicas explicadas de forma simples, direta e visual',
                $oldHighlights[2],
            ];
        }

        $faq = $product->faq ?? [];

        foreach ($faq as $index => $item) {
            if (($item['question'] ?? null) === 'Posso ver algumas páginas antes de comprar?'
                && ($item['answer'] ?? null) === 'Sim. A prévia gratuita nesta página contém páginas selecionadas do próprio ebook, incluindo sumário e exemplos de capítulos.') {
                $faq[$index]['answer'] = 'Sim. A prévia gratuita nesta página contém páginas selecionadas do próprio ebook, com exemplos de capítulos e dúvidas frequentes da obra.';
                $changes['faq'] = $faq;
            }
        }

        if (! collect($faq)->contains('question', 'Possuirei acesso vitalício?')) {
            $faq[] = ['question' => 'Possuirei acesso vitalício?', 'answer' => 'Sim. O ebook permanece na sua biblioteca sem prazo de expiração enquanto sua conta estiver ativa; você pode ler online e baixar o PDF quando precisar. Melhorias e atualizações de normas incorporadas a esta edição são disponibilizadas sem custo adicional. Novas edições podem ser vendidas separadamente.'];
            $changes['faq'] = $faq;
        }

        if ($changes !== []) {
            $product->update($changes);
        }
    }
}
