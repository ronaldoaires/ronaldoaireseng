<x-legal.page
    title="Termos de Uso"
    description="Regras de uso do site, da conta, da compra de ebooks e planilhas, da versão digital e dos direitos do consumidor.">

    <x-legal.section title="1. Sobre estes termos">
        <p>
            Estes Termos de Uso regulam o acesso e a utilização do site <strong>{{ config('app.name') }}</strong>,
            mantido por <strong>{{ config('legal.owner') }}</strong>@if (config('legal.document')), inscrito(a) sob o documento {{ config('legal.document') }}@endif,
            com sede em {{ config('legal.city') }}. Ao criar uma conta ou fazer uma compra, você declara que leu e aceita estas regras,
            a <a href="{{ route('legal.license') }}">Licença de Uso</a> e a <a href="{{ route('legal.privacy') }}">Política de Privacidade</a>.
        </p>
        <p>
            O site oferece conteúdo técnico de engenharia civil: ebooks (com arquivo PDF e, quando indicado, versão digital para leitura online),
            planilhas de cálculo, ferramentas e a contratação de serviços de engenharia.
        </p>
    </x-legal.section>

    <x-legal.section title="2. Sua conta">
        <ul>
            <li>Você deve ter 18 anos ou mais e informar dados verdadeiros e atualizados.</li>
            <li>Você pode entrar com e-mail e senha ou com a sua conta Google. O cadastro com e-mail só é ativado depois da confirmação pelo link enviado.</li>
            <li>A conta é pessoal e intransferível. Você é responsável por manter a senha em sigilo e por tudo o que acontece na sua conta.</li>
            <li>Podemos desativar contas que violem estes termos, como em casos de fraude, compartilhamento de acesso ou redistribuição de material.</li>
            <li>Você pode pedir o encerramento da conta em <strong>Minha conta &gt; Encerrar conta</strong>. A conta continua ativa por 30 dias, período em que você pode cancelar o pedido. Depois disso, o acesso à biblioteca é perdido.</li>
        </ul>
    </x-legal.section>

    <x-legal.section title="3. Ebooks, planilhas e versão digital">
        <ul>
            <li>Cada produto é vendido com pagamento único. Não há assinatura nem cobrança recorrente.</li>
            <li>Depois da confirmação do pagamento, o produto fica na sua <strong>biblioteca</strong>, dentro da conta, para baixar quantas vezes precisar.</li>
            <li>Quem compra um ebook com versão digital recebe o PDF e o acesso à leitura online dos capítulos, enquanto a conta estiver ativa.</li>
            <li>Correções e atualizações da mesma edição ou versão são liberadas sem custo. Novas edições podem ser vendidas separadamente.</li>
            <li>Produto retirado da loja continua disponível na biblioteca de quem já comprou.</li>
            <li>As planilhas indicam o programa e o sistema compatíveis na página do produto. Confira antes de comprar.</li>
        </ul>
    </x-legal.section>

    <x-legal.section title="4. Responsabilidade técnica">
        <p>
            Ebooks, planilhas e calculadoras são material de estudo e de apoio ao projeto, baseados nas normas técnicas brasileiras
            vigentes na data da publicação. Eles <strong>não substituem o projeto de um profissional habilitado</strong>.
            A verificação dos resultados, a escolha das hipóteses de cálculo e a responsabilidade técnica pela obra ou projeto
            (ART/RRT) são de quem utiliza o material.
        </p>
    </x-legal.section>

    <x-legal.section title="5. Pagamentos">
        <p>
            Os pagamentos são processados pelo <strong>Mercado Pago</strong>, em reais, por Pix ou cartão. Os dados do seu cartão são informados
            diretamente no ambiente do Mercado Pago e <strong>não são armazenados por nós</strong>.
            O uso do serviço de pagamento também está sujeito aos termos do Mercado Pago.
        </p>
    </x-legal.section>

    <x-legal.section title="6. Arrependimento e reembolso">
        <p>
            Como a compra é feita pela internet, você pode desistir em até <strong>7 dias corridos</strong> após a confirmação do pagamento,
            com devolução do valor pago, conforme o art. 49 do Código de Defesa do Consumidor.
            Para isso, escreva para <a href="mailto:{{ config('legal.email') }}">{{ config('legal.email') }}</a> informando o e-mail da conta.
        </p>
        <p>
            Com o reembolso, o acesso ao produto é encerrado e os arquivos baixados devem ser apagados.
            Pedidos repetidos de reembolso que indiquem abuso podem levar ao bloqueio da conta.
        </p>
    </x-legal.section>

    <x-legal.section title="7. Uso adequado">
        <p>Não é permitido:</p>
        <ul>
            <li>compartilhar a conta, os arquivos ou o acesso à versão digital com outras pessoas;</li>
            <li>usar robôs, scripts ou qualquer meio automatizado para baixar arquivos ou copiar o conteúdo do site;</li>
            <li>remover ou alterar avisos de licença, autoria ou identificação do comprador;</li>
            <li>tentar acessar áreas restritas, sobrecarregar ou comprometer a segurança do sistema.</li>
        </ul>
        <p>Os downloads e acessos ficam registrados para proteger o seu material e o do autor.</p>
    </x-legal.section>

    <x-legal.section title="8. Propriedade intelectual">
        <p>
            Textos, figuras, planilhas, códigos e o layout do site pertencem a {{ config('legal.owner') }} e são protegidos pela
            Lei de Direitos Autorais (Lei 9.610/1998). A compra dá direito ao uso descrito na
            <a href="{{ route('legal.license') }}">Licença de Uso</a>, não à propriedade do conteúdo.
        </p>
    </x-legal.section>

    <x-legal.section title="9. Disponibilidade">
        <p>
            Trabalhamos para manter o site disponível, mas ele pode ficar fora do ar por manutenção ou por motivos fora do nosso controle.
            Recomendamos manter uma cópia dos arquivos que você comprou.
        </p>
    </x-legal.section>

    <x-legal.section title="10. Alterações destes termos">
        <p>
            Podemos atualizar estes termos. A data da última atualização aparece no topo desta página.
            Quando houver mudanças relevantes, pediremos que você aceite a nova versão na próxima compra.
        </p>
    </x-legal.section>

    <x-legal.section title="11. Contato e foro">
        <p>
            Dúvidas ou solicitações: <a href="mailto:{{ config('legal.email') }}">{{ config('legal.email') }}</a>.
            Para resolver conflitos, fica eleito o foro do domicílio do consumidor, conforme a legislação brasileira.
        </p>
    </x-legal.section>
</x-legal.page>
