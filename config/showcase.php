<?php

/*
|--------------------------------------------------------------------------
| Services showcase
|--------------------------------------------------------------------------
|
| Fixed texts of the engineering/technology services pages and the
| identity of the company behind them. Everything the visitor reads is
| in Portuguese; change it here without touching the views.
|
*/

return [

    // Private contact data: read only on the server, never printed in a page.
    'contact' => [
        // Digits with country code, e.g. 5566981068854. Used by the /whatsapp redirect.
        'whatsapp' => env('SHOWCASE_WHATSAPP'),
        // Inbox that receives quote requests and messages (defaults to the mail sender).
        'leads_email' => env('SHOWCASE_LEADS_EMAIL', env('MAIL_FROM_ADDRESS')),
        // Promise shown after sending and in the auto-reply.
        'reply_time' => 'em até 1 dia útil',
    ],

    'company' => [
        'name' => 'Ronaldo Aires',
        'area_served' => 'Brasil',
        'service_mode' => 'Atendimento online para todo o Brasil',
        // Short version for tight spots, like the footer badge.
        'service_mode_short' => 'Online para todo o Brasil',
    ],

    // The person behind the services. Each area shows the profile that
    // matters to its client: engineering shows the CREA registry and
    // engineering degrees; technology shows the developer side.
    'person' => [
        'name' => 'Ronaldo Aires',
        'links' => [
            'Instagram' => 'https://www.instagram.com/ronaldoaireseng/',
            'LinkedIn' => 'https://www.linkedin.com/in/ronaldoaires/',
            'GitHub' => 'https://github.com/ronaldoaires',
            'YouTube' => 'https://www.youtube.com/@ronaldoaireseng',
        ],
    ],

    // Keyed by App\Enums\ServiceArea values.
    'profiles' => [
        'engineering' => [
            'title' => 'Engenheiro Civil',
            'badge' => 'CREA-MT 62629',
            'headline' => 'Responsável técnico pelos projetos, com registro no CREA.',
            'credentials' => [
                'Engenharia Civil - UNEMAT',
                'MBA em Gestão de Obras na Construção Civil',
                'Pós-graduação em BIM em Gerenciamento de Projetos e Qualidade de Obras',
                'Projetos em BIM com Revit, Eberick e AltoQi Builder',
            ],
        ],
        'technology' => [
            'title' => 'Desenvolvedor Full Stack',
            'badge' => 'Laravel · Flutter · Python',
            'headline' => 'Sites, sistemas e aplicativos desenvolvidos de ponta a ponta, do banco de dados à tela.',
            'credentials' => [
                'Tecnólogo em Sistemas para Internet - IFMT',
                'Graduação em Ciência de Dados - UNICID',
                'Pós-graduação em Inteligência Artificial e Análise de Dados',
                'Pós-graduação em Ciência de Dados e Big Data Analytics',
                'Instrutor de cursos de programação na Udemy e no YouTube',
                'Sistemas com pagamentos online, APIs, painéis administrativos e apps',
            ],
        ],
    ],

    // Steps of the "Como funciona" section, shared by every service.
    'steps' => [
        ['title' => 'Contato',     'text' => 'Você conta o que precisa pelo formulário ou WhatsApp.'],
        ['title' => 'Orçamento',   'text' => 'Analiso o pedido e envio uma proposta clara, com prazo e valor.'],
        ['title' => 'Execução',    'text' => 'O projeto é desenvolvido com acompanhamento e revisões combinadas.'],
        ['title' => 'Entrega',     'text' => 'Você recebe os entregáveis e as condições de suporte combinadas na proposta.'],
    ],

    // Concise outcomes and next step for each individual service page.
    'service_sales' => [
        'projeto-estrutural' => [
            'benefits' => [
                ['title' => 'Cada elemento no lugar certo', 'text' => 'Fundações, pilares, vigas e lajes definidos antes de começar a execução.'],
                ['title' => 'Materiais com referência', 'text' => 'Quantitativos para ajudar a orçar concreto, aço e formas antes das compras.'],
                ['title' => 'Equipe com orientação', 'text' => 'Plantas e detalhes de armaduras mostram como executar a estrutura projetada.'],
            ],
            'cta_heading' => 'Sua obra precisa de uma estrutura definida antes da execução',
            'cta_text' => 'Conte o que pretende construir e envie as informações que já possui. Analiso o pedido e preparo uma proposta com escopo, prazo e valor para o projeto estrutural.',
        ],
        'projeto-eletrico' => [
            'benefits' => [
                ['title' => 'Pontos onde fazem sentido', 'text' => 'Tomadas, iluminação e equipamentos planejados com a distribuição dos ambientes.'],
                ['title' => 'Circuitos dimensionados', 'text' => 'Cargas e proteções definidas para orientar a instalação e futuras ampliações previstas.'],
                ['title' => 'Execução mais clara', 'text' => 'Plantas, diagramas e lista de materiais para a equipe trabalhar com referência.'],
            ],
            'cta_heading' => 'Planeje a instalação elétrica antes de fechar as paredes',
            'cta_text' => 'Envie a planta e conte quais equipamentos pretende usar. Receba uma proposta com escopo, prazo e valor para o projeto elétrico.',
        ],
        'projeto-arquitetonico' => [
            'benefits' => [
                ['title' => 'Ambientes pensados para o uso', 'text' => 'Distribuição, circulação, iluminação e ventilação analisadas desde o estudo inicial.'],
                ['title' => 'Visualize antes de construir', 'text' => 'Plantas e imagens 3D ajudam a avaliar escolhas antes da execução.'],
                ['title' => 'Base para os demais projetos', 'text' => 'A arquitetura orienta estrutura, elétrica e instalações hidrossanitárias.'],
            ],
            'cta_heading' => 'Sua ideia merece um projeto que caiba no terreno e na rotina',
            'cta_text' => 'Conte como quer usar os ambientes e envie as informações do terreno. Analiso a necessidade e preparo uma proposta para o projeto arquitetônico.',
        ],
        'projeto-hidrossanitario' => [
            'benefits' => [
                ['title' => 'Pontos de água planejados', 'text' => 'Traçados e dimensionamento definidos para os usos previstos na edificação.'],
                ['title' => 'Esgoto e ventilação', 'text' => 'Tubulações, caixas e ventilação detalhadas para orientar a execução.'],
                ['title' => 'Chuva considerada no projeto', 'text' => 'Calhas e condutores dimensionados conforme as características da cobertura.'],
            ],
            'cta_heading' => 'Defina as instalações antes que a obra esconda os tubos',
            'cta_text' => 'Envie a planta e informe os pontos de água, o tipo de aquecimento e as condições de esgoto. Receba uma proposta com o escopo do projeto.',
        ],
        'desenvolvimento-de-sites' => [
            'benefits' => [
                ['title' => 'Mensagem clara', 'text' => 'Páginas que apresentam seus serviços e mostram ao visitante o próximo passo.'],
                ['title' => 'Funciona no celular', 'text' => 'Layout responsivo e conteúdo pensado para leitura e contato em telas pequenas.'],
                ['title' => 'Conteúdo sob seu controle', 'text' => 'Painel para atualizar informações conforme o escopo definido na proposta.'],
            ],
            'cta_heading' => 'Seu site deve explicar o que você faz e facilitar o contato',
            'cta_text' => 'Conte sobre o negócio, as páginas necessárias e o que o visitante deve fazer. Preparo uma proposta com funcionalidades, prazo e valor.',
        ],
        'desenvolvimento-de-aplicativos' => [
            'benefits' => [
                ['title' => 'Uma tarefa bem resolvida', 'text' => 'O fluxo principal é definido antes das telas e da programação.'],
                ['title' => 'Android e iPhone', 'text' => 'A viabilidade de cada recurso é avaliada para as duas plataformas.'],
                ['title' => 'Escopo antes do código', 'text' => 'Protótipo, integrações e publicação combinados na proposta.'],
            ],
            'cta_heading' => 'Vamos transformar a ideia do app em um escopo concreto',
            'cta_text' => 'Descreva quem vai usar o aplicativo e qual tarefa ele precisa resolver. Receba uma proposta com etapas, integrações e prazo.',
        ],
        'desenvolvimento-de-software' => [
            'benefits' => [
                ['title' => 'Processo entendido primeiro', 'text' => 'Mapeamento do trabalho atual e dos pontos que causam retrabalho.'],
                ['title' => 'Primeira entrega útil', 'text' => 'Prioridade para o fluxo que já pode ser testado pela equipe.'],
                ['title' => 'Evolução planejada', 'text' => 'Novas funções e integrações definidas por etapa, conforme a necessidade.'],
            ],
            'cta_heading' => 'Mostre o processo que hoje depende de planilhas e repasses manuais',
            'cta_text' => 'Conte onde a informação se perde e o que a equipe precisa acompanhar. Analiso o fluxo e proponho um primeiro escopo com prazo e valor.',
        ],
    ],

    // Intro of each area page, keyed by App\Enums\ServiceArea values.
    'areas' => [
        'engineering' => [
            'heading' => 'Serviços de engenharia',
            'headline' => 'Projetos de engenharia completos em BIM, compatibilizados em 3D.',
            'description' => 'Projetos arquitetônico, estrutural, elétrico e hidrossanitário feitos em BIM, compatibilizados entre si e prontos para aprovação e execução.',
            'icon' => 'building-office-2',
        ],
        'technology' => [
            'heading' => 'Serviços de tecnologia',
            'headline' => 'Sites, sistemas e aplicativos modernos, feitos para o seu negócio crescer.',
            'description' => 'Desenvolvimento de sites, sistemas web, softwares sob medida e aplicativos, com foco em desempenho, segurança e resultado.',
            'icon' => 'code-bracket',
        ],
    ],

    // 3D renders from the ebook "Concreto Armado sem Mistério" (public/images/renders).
    'renders' => [
        // Service slug => render shown instead of the uploaded cover photo.
        'services' => [
            'projeto-estrutural' => 'images/estrutura-esforcos-eberick-sem-fundo.png',
        ],

        // Placeholder covers for products without one (picked by product id).
        'product_fallbacks' => [
            'images/renders/ch08-tipos-laje.webp',
            'images/renders/ch09-viga.webp',
            'images/renders/ch10-emenda-pilar.webp',
            'images/renders/ch12-escada-plissada.webp',
            'images/renders/ch17-escoramento.webp',
            'images/renders/ch07-cobrimento.webp',
        ],

        // Home strip "inside the ebook": render => chapter question.
        'ebook_slug_prefix' => 'concreto-armado',
        'ebook_chapters' => [
            'images/renders/ch02-caminho-cargas.webp' => 'Para onde vai o peso da edificação?',
            'images/renders/ch03-cargas-laje.webp' => 'Que cargas a estrutura carrega?',
            'images/renders/ch06-bitolas.webp' => 'Aço: CA-50, CA-60 e as bitolas',
            'images/renders/ch07-cobrimento.webp' => 'Quanto de concreto cobre o aço?',
            'images/renders/ch08-tipos-laje.webp' => 'Lajes: maciça, treliçada ou nervurada?',
            'images/renders/ch09-viga.webp' => 'Vigas: por que o aço fica embaixo?',
            'images/renders/ch10-emenda-pilar.webp' => 'Pilares: por que tão cheios de estribo?',
            'images/renders/ch11-fundacoes.webp' => 'Fundações: sapata, bloco, estaca e radier',
            'images/renders/ch17-escoramento.webp' => 'Da fôrma à desforma',
        ],
    ],

];
