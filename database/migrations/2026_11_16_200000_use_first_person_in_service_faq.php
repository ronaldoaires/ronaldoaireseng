<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The site belongs to one professional: service FAQs seeded before this change
 * spoke in the plural ("Vocês fazem", "Avaliamos"). Rewrite them in the first
 * person singular. Only exact seeded phrases are touched; admin edits stay.
 */
return new class extends Migration
{
    private const REPLACEMENTS = [
        'também fazemos o arquitetônico' => 'também faço o arquitetônico',
        'Vocês atendem obras' => 'Você atende obras',
        'Vocês fazem projeto elétrico para reforma?' => 'Você faz projeto elétrico para reforma?',
        'Avaliamos a instalação existente e projetamos a adequação' => 'Avalio a instalação existente e projeto a adequação',
        'Vocês fazem projeto de reforma e ampliação?' => 'Você faz projeto de reforma e ampliação?',
        'Levantamos o que já existe e projetamos' => 'Levanto o que já existe e projeto',
        'avaliamos a instalação e indicamos' => 'avalio a instalação e indico',
        'Vocês dimensionam' => 'Você dimensiona',
        'e orientamos como melhorar' => 'e oriento como melhorar',
        'Ajudamos a escolher e configuramos tudo.' => 'Ajudo a escolher e configuro tudo.',
        'Vocês fazem loja virtual?' => 'Você faz loja virtual?',
        'Vocês publicam o app nas lojas?' => 'Você publica o app nas lojas?',
        'Sim, cuidamos da publicação' => 'Sim, cuido da publicação',
        'Também podemos desenvolver' => 'Também posso desenvolver',
        'Começamos pelo que' => 'Começo pelo que',
        'também desenvolvemos um aplicativo' => 'também desenvolvo um aplicativo',
        'enviamos uma proposta com escopo' => 'envio uma proposta com escopo',
    ];

    public function up(): void
    {
        DB::table('services')->whereNotNull('faq')->orderBy('id')->each(function (object $service) {
            $faq = json_decode($service->faq, true);

            if (! is_array($faq)) {
                return;
            }

            $updated = array_map(fn (array $item) => [
                ...$item,
                'question' => strtr($item['question'] ?? '', self::REPLACEMENTS),
                'answer'   => strtr($item['answer'] ?? '', self::REPLACEMENTS),
            ], $faq);

            if ($updated !== $faq) {
                DB::table('services')->where('id', $service->id)->update([
                    'faq' => json_encode($updated, JSON_UNESCAPED_UNICODE),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Copy change only; nothing to restore.
    }
};
