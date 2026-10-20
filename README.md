# Rodrigo Lima - Jornada de Venda Digital

**Estudo de microserviços em evolução**, adaptado por Rodrigo Lima.

O portfólio adapta um estudo guiado por curso da Alura para apresentar uma jornada de venda de produto digital: checkout com pagamento simulado e entrega de acesso a cursos. A base e as contribuições originais continuam atribuídas às suas autorias; este README não declara todo o código como autoral de Rodrigo. A marca textual é **Rodrigo Lima - Jornada de Venda Digital**. Este repositório único registra um snapshot da working tree local; a origem local foi preservada.

Veja [procedência e atribuição](docs/ORIGIN.md), [arquitetura](docs/ARCHITECTURE.md), [fluxo de desenvolvimento](docs/DEVELOPMENT.md), [spec 001 — jornada de venda](docs/specs/001-sales-journey.md) e [spec 002 — matrícula no consumer Acadêmico](docs/specs/002-enrollment-use-case.md).

## Contextos do fluxo

- **Marketing — interesse e leads:** recebe o nome e o e-mail do interessado e converte o lead quando recebe o evento de matrícula. Mantém o estado do lead; não controla pagamento nem acesso a cursos.
- **Financeiro — checkout e pagamento simulado:** recebe os dados do checkout, agenda `process_payment` e, ao concluir a simulação, publica o evento de sucesso existente. Não usa adquirente nem confirma uma transação financeira real.
- **Acadêmico/Entrega — matrícula e acesso:** consome o evento, cria a conta do aluno e disponibiliza login e cursos. Não decide o estado do pagamento nem converte leads.

Não há serviço independente de pedidos: o checkout coordena os contextos existentes, e cada serviço mantém seus próprios dados e responsabilidades.

## Jornada técnica atual

1. O front-end envia `fullName` e `email` para `POST /mkt/leads` através do gateway.
2. O checkout envia dados fictícios para `POST /financeiro/clients`. HTTP `201` indica que o processamento foi agendado, não que a entrega já terminou.
3. O worker Financeiro processa `process_payment` e publica `client_enrolled` com `cpf`, `name` e `email` no RabbitMQ.
4. Marketing consome o evento pela fila `lead_convertion` e converte o lead; o consumer Acadêmico usa a fila `student_enrollment` e cria o aluno.
5. A entrega pode ser conferida pelo login em `/academico/login` e pelo acesso autenticado a `/academico/cursos`.

O pagamento é simulado e a entrega digital é a matrícula/acesso existente. Não inserir dados pessoais ou de pagamento reais; o formulário contém campos de cartão apenas por causa do fluxo de demonstração.

## Executar localmente

Na raiz do repositório, com Docker Compose disponível e as portas necessárias livres:

```powershell
docker compose up --build
```

O Compose raiz sobe front-end, Marketing, Financeiro, RabbitMQ, gateway Nginx e serviços Acadêmicos com Postgres. O gateway local é exposto na porta 80. Para encerrar a stack, use `Ctrl+C` no terminal do Compose.

O Compose declara volumes nomeados para MongoDB, RabbitMQ e Postgres. Como o pool de endereços padrão se esgotou nesta máquina, a rede `default` usa `JORNADA_DOCKER_SUBNET`, com padrão `10.254.240.0/24`. Essa faixa foi validada aqui; em outra máquina, pode ser necessário escolher uma sub-rede diferente que não conflite com as redes locais. A stack antiga foi somente parada com `docker compose stop`; seus containers e dados permanecem. Os dados antigos não foram migrados para os novos volumes nomeados.

Em outro terminal, com a stack pronta, o smoke sintético pode ser executado assim:

```powershell
powershell -NoProfile -File ./scripts/smoke.ps1
```

O script usa o gateway em `http://127.0.0.1:80` por padrão; `-Port` permite escolher outra porta. Gera e-mail `example.invalid`, nome fictício, CPF com dígito inválido e dados de cartão fictícios. Confirma HTTP `201` nos dois POSTs e tenta o login até cerca de 45 segundos, com timeout de 15 segundos por tentativa, esperando HTTP `200` com JWT de três segmentos. Deixa registros fictícios nos bancos locais (lead no MongoDB e aluno no Postgres); não os remove. Não verifica diretamente a conversão do lead nem a entrega de e-mail.

No consumer Acadêmico, a senha de demonstração atual é `123456`; ela serve somente para o smoke local e não é uma credencial segura para uso real. O Compose usa `MAILER_DSN=null://null` como padrão, então o e-mail não chega a uma caixa postal. Isso permite exercitar o fluxo sem configurar SMTP, mas não comprova entrega de notificação.

## Organização do repositório

O projeto está consolidado em um único repositório Git com cinco pastas normais, sem submódulos:

| Pasta | Papel no fluxo |
|---|---|
| `front-end` | Interface Angular de checkout e cursos. |
| `mkt-node` | API e estado de leads; conversão assíncrona por evento. |
| `financeiro-php` | Checkout, agendamento/processamento simulado e publicação do evento. |
| `academico-php` | Consumer RabbitMQ e matrícula; persistência RedBean e notificação Symfony. |
| `academico-php-web` | Login do aluno e acesso aos cursos. |

As origens, SHAs de referência e autorias das cinco pastas estão registradas em [docs/ORIGIN.md](docs/ORIGIN.md). O snapshot não combina os históricos Git dos submódulos. Rodrigo declarou possuir permissão para publicar esta adaptação em 2026-10-20; veja o registro em [docs/ORIGIN.md](docs/ORIGIN.md).

## Limites conhecidos

- O consumer Acadêmico usa `no_ack: true`: não há confirmação manual de processamento nem retry controlado pelo consumer.
- O publicador cria `AMQPMessage` sem marcar `delivery_mode` persistente; exchange/fila duráveis não tornam a mensagem persistente.
- A stack conserva versões antigas herdadas do estudo, incluindo imagens Node 16 e PHP 8.0. É uma demonstração local, sem alegação de prontidão para produção.
- O smoke cobre criação do lead, agendamento do pagamento e eventual login do aluno; não substitui verificação independente do estado convertido no Marketing, entrega de e-mail ou resiliência de mensageria.
