<x-legal.page
    title="Licença de Uso"
    description="O que você pode e o que não pode fazer com os ebooks, planilhas e a versão digital comprados no site.">

    <x-legal.section title="1. O que a compra garante">
        <p>
            Ao comprar um ebook ou uma planilha, você recebe uma licença <strong>pessoal, não exclusiva e intransferível</strong>
            para usar o material, sem prazo de validade, enquanto respeitar esta licença.
        </p>
    </x-legal.section>

    <x-legal.section title="2. Você pode">
        <ul>
            <li>ler, estudar e imprimir o ebook para uso próprio;</li>
            <li>usar as planilhas em quantos projetos quiser, inclusive projetos feitos para seus clientes;</li>
            <li>guardar cópias de segurança dos arquivos nos seus dispositivos;</li>
            <li>citar trechos curtos com indicação da fonte, como em trabalhos acadêmicos.</li>
        </ul>
    </x-legal.section>

    <x-legal.section title="3. Você não pode">
        <ul>
            <li>compartilhar, enviar, publicar ou disponibilizar os arquivos, no todo ou em parte, por qualquer meio (grupos, redes sociais, nuvem, sites);</li>
            <li>revender, alugar, sublicenciar ou incluir o material em cursos, apostilas ou produtos, pagos ou gratuitos;</li>
            <li>remover proteções, senhas, marcas d'água ou a identificação do comprador;</li>
            <li>copiar fórmulas, macros ou códigos das planilhas para criar outro produto;</li>
            <li>dar acesso à sua conta ou à versão digital para outra pessoa.</li>
        </ul>
    </x-legal.section>

    <x-legal.section title="4. Uso em empresas e turmas">
        <p>
            Cada licença vale para uma pessoa. Para equipes, escritórios ou turmas de curso, fale comigo em
            <a href="mailto:{{ config('legal.email') }}">{{ config('legal.email') }}</a> para uma licença coletiva.
        </p>
    </x-legal.section>

    <x-legal.section title="5. Identificação e violação">
        <p>
            Os arquivos e a versão digital podem conter a identificação de quem comprou, e os downloads ficam registrados.
            O uso fora desta licença leva ao bloqueio da conta, sem reembolso, e pode gerar responsabilização nos termos da
            Lei 9.610/1998 (Direitos Autorais) e do art. 184 do Código Penal.
        </p>
    </x-legal.section>

    <x-legal.section title="6. Responsabilidade técnica">
        <p>
            O material é de apoio e estudo. A conferência dos resultados e a responsabilidade técnica por projetos e obras
            são do profissional que o utiliza. Veja também os <a href="{{ route('legal.terms') }}">Termos de Uso</a>.
        </p>
    </x-legal.section>
</x-legal.page>
