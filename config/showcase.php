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
        'whatsapp'    => env('SHOWCASE_WHATSAPP'),
        // Inbox that receives quote requests and messages (defaults to the mail sender).
        'leads_email' => env('SHOWCASE_LEADS_EMAIL', env('MAIL_FROM_ADDRESS')),
        // Promise shown after sending and in the auto-reply.
        'reply_time'  => 'em até 1 dia útil',
    ],

    'company' => [
        'name'         => 'Ronaldo Aires',
        'area_served'  => 'Brasil',
        'service_mode' => 'Atendimento online para todo o Brasil',
        // Short version for tight spots, like the footer badge.
        'service_mode_short' => 'Online para todo o Brasil',
    ],

    // The person behind the services. Each area shows the profile that
    // matters to its client: engineering shows the CREA registry and
    // engineering degrees; technology shows the developer side.
    'person' => [
        'name'  => 'Ronaldo Aires',
        'links' => [
            'Instagram' => 'https://www.instagram.com/ronaldoaireseng/',
            'LinkedIn'  => 'https://www.linkedin.com/in/ronaldoaires/',
            'GitHub'    => 'https://github.com/ronaldoaires',
        ],
    ],

    // Keyed by App\Enums\ServiceArea values.
    'profiles' => [
        'engineering' => [
            'title'    => 'Engenheiro Civil',
            'badge'    => 'CREA-MT 62629',
            'headline' => 'Responsável técnico pelos projetos, com registro no CREA.',
            'credentials' => [
                'Engenharia Civil - UNEMAT',
                'MBA em Gestão de Obras na Construção Civil',
                'Pós-graduação em BIM em Gerenciamento de Projetos e Qualidade de Obras',
                'Projetos em BIM com Revit, Eberick e AltoQi Builder',
            ],
        ],
        'technology' => [
            'title'    => 'Desenvolvedor Full Stack',
            'badge'    => 'Laravel · Flutter · Python',
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
        ['title' => 'Entrega',     'text' => 'Você recebe tudo pronto e conta com suporte após a entrega.'],
    ],

    // Intro of each area page, keyed by App\Enums\ServiceArea values.
    'areas' => [
        'engineering' => [
            'heading'     => 'Serviços de engenharia',
            'headline'    => 'Projetos de engenharia completos em BIM, compatibilizados em 3D.',
            'description' => 'Projetos arquitetônico, estrutural, elétrico e hidrossanitário feitos em BIM, compatibilizados entre si e prontos para aprovação e execução.',
            'icon'        => 'building-office-2',
        ],
        'technology' => [
            'heading'     => 'Serviços de tecnologia',
            'headline'    => 'Sites, sistemas e aplicativos modernos, feitos para o seu negócio crescer.',
            'description' => 'Desenvolvimento de sites, sistemas web, softwares sob medida e aplicativos, com foco em desempenho, segurança e resultado.',
            'icon'        => 'code-bracket',
        ],
    ],

    // 3D renders from the ebook "Concreto Armado sem Mistério" (public/images/renders).
    'renders' => [
        // Service slug => render shown instead of the uploaded cover photo.
        'services' => [
            'projeto-estrutural' => 'images/renders/ch02-caminho-cargas.webp',
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
            'images/renders/ch03-cargas-laje.webp'    => 'Que cargas a estrutura carrega?',
            'images/renders/ch06-bitolas.webp'        => 'Aço: CA-50, CA-60 e as bitolas',
            'images/renders/ch07-cobrimento.webp'     => 'Quanto de concreto cobre o aço?',
            'images/renders/ch08-tipos-laje.webp'     => 'Lajes: maciça, treliçada ou nervurada?',
            'images/renders/ch09-viga.webp'           => 'Vigas: por que o aço fica embaixo?',
            'images/renders/ch10-emenda-pilar.webp'   => 'Pilares: por que tão cheios de estribo?',
            'images/renders/ch11-fundacoes.webp'      => 'Fundações: sapata, bloco, estaca e radier',
            'images/renders/ch17-escoramento.webp'    => 'Da fôrma à desforma',
        ],
    ],

];
