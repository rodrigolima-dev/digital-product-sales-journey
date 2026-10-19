# 001 — Jornada de venda de produto digital

**Status:** implementação local validada parcialmente; repositório único consolidado; permissões para publicação ainda pendentes.

## Contexto e escopo

Adaptar o fluxo de checkout de cursos já existente para demonstrar a venda de um produto digital, com pagamento simulado e entrega de acesso. A matrícula/acesso é a entrega do produto. Esta especificação não introduz catálogo novo, produto físico, estoque, frete, provedor de pagamento real, novos serviços, endpoints ou eventos.

A marca textual é **Rodrigo Lima - Jornada de Venda Digital**. A organização atual reúne `front-end`, `mkt-node`, `financeiro-php`, `academico-php` e `academico-php-web` como cinco pastas normais em um único repositório. O conteúdo consolidado é um snapshot da working tree local; veja a procedência e os SHAs em `docs/ORIGIN.md`.

## Limites de domínio atuais

| Serviço | Responsabilidade neste fluxo | Fora do limite |
|---|---|---|
| Marketing (`mkt-node`) | Criar lead interessado e convertê-lo em cliente pelo evento recebido, usando e-mail. | Pagamento e concessão de acesso. |
| Financeiro (`financeiro-php`) | Receber os dados do checkout, agendar a simulação de pagamento e publicar o evento de sucesso atual. | Gerenciar leads, alunos ou acesso a cursos; não há integração com adquirente. |
| Acadêmico/Entrega (`academico-php`, `academico-php-web`) | Consumir a matrícula, criar a conta de aluno e permitir login/acesso aos cursos. | Decidir pagamento ou gerir o estado do lead. |

Não criar um contexto ou serviço independente de pedidos nesta etapa. O checkout coordena o fluxo existente; cada serviço mantém seus próprios conceitos.

## Fluxo e contratos observados

1. O Angular envia os dados pessoais para `POST /mkt/leads` como `{ nome, email }`. O roteador de Marketing lê `{ fullName, email }`; há incompatibilidade de campo. Adaptar o payload na fronteira existente para enviar `fullName` a partir do campo de tela `nome`, sem adicionar endpoint ou duplicar campos.
2. O checkout envia pagamento para `POST /financeiro/clients` (o gateway encaminha para `POST /clients`). Financeiro agenda `process_payment` e responde `201` antes do processamento assíncrono terminar. A interface já informa que o pagamento está sendo processado.
3. Ao concluir a simulação, Financeiro publica no exchange fanout RabbitMQ `client_enrolled` o JSON com `cpf`, `name` e `email`. Acadêmico consome pela fila `student_enrollment` e cria o aluno; Marketing consome pela fila `lead_convertion` e converte o lead pelo e-mail.
4. Acadêmico/Entrega disponibiliza login em `/login` e cursos em `/cursos`. O campo `name` do evento alimenta o nome do aluno; não confundir com `fullName`, que é o contrato HTTP de Marketing.

O evento, exchange, filas, rotas e payloads acima são os contratos existentes e permanecem a referência desta adaptação. `201` significa que a simulação foi agendada, não que a entrega já foi concluída. Usar apenas dados sintéticos: a tela atual coleta PAN e CVV, mas não se deve inserir dados reais nem alegar processamento financeiro real.

## Critérios de aceite

- O checkout envia `fullName` e `email` ao contrato existente de Marketing; um envio com nome e e-mail sintéticos cria lead com nome íntegro e status interessado.
- O checkout chama o endpoint de pagamento existente; recebe `201` ao agendar a simulação e apresenta estado de processamento, sem afirmar confirmação síncrona.
- Após `process_payment`, o exchange `client_enrolled` recebe o evento com `cpf`, `name` e `email`; os consumidores existentes recebem o evento sem alteração de nome ou estrutura.
- Após o consumo, o lead passa a cliente e o Acadêmico cria a conta correspondente; o usuário sintético consegue autenticar-se em `/login` e consultar `/cursos`.
- O cenário de ponta a ponta é verificável com logs/estado dos serviços locais e dados sintéticos; não depende de adquirente ou pagamento real.

## Marca e procedência

A interface já usa a identidade Rodrigo Lima e uma oferta demonstrativa. Não sugerir vínculo ou endosso da Alura nem inventar preço ou promessa comercial. Manter os créditos à origem, as autorias e as licenças aplicáveis em `docs/ORIGIN.md`, mesmo após converter as cinco pastas para diretórios comuns. Identificar claramente as adaptações de Rodrigo e não apresentar o código de estudo como criação original. A publicação independente depende de esclarecimento das permissões de uso e redistribuição.
