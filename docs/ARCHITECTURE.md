# Arquitetura — Rodrigo Lima - Jornada de Venda Digital

## Escopo e estado

O sistema demonstra uma venda digital com pagamento simulado e entrega de acesso a cursos. A arquitetura descrita é pragmática: três contextos existentes cooperam por contratos HTTP e um evento RabbitMQ, sem serviço novo de pedidos.

O projeto está consolidado em um único repositório Git com cinco pastas normais: `front-end`, `mkt-node`, `financeiro-php`, `academico-php` e `academico-php-web`. O conteúdo é um snapshot da working tree local; a origem local foi preservada, os históricos Git dos submódulos não foram combinados. As referências de procedência estão em [ORIGIN.md](ORIGIN.md).

## Limites de contexto

| Contexto | Responsabilidade | Fora do limite |
|---|---|---|
| Marketing (`mkt-node`) | Receber leads, manter seu estado no MongoDB e converter o lead quando recebe o evento de matrícula. | Simular/capturar pagamento ou conceder acesso. |
| Financeiro (`financeiro-php`) | Receber o checkout, agendar `process_payment` e publicar o evento ao concluir a simulação. | Ser dono do lead, do aluno ou do acesso; integração com adquirente. |
| Acadêmico/Entrega (`academico-php`, `academico-php-web`) | Consumir matrícula, criar aluno no Postgres e oferecer login e acesso aos cursos. | Decidir o resultado do pagamento ou converter lead. |

O `front-end` é a interface de entrada e checkout, não um contexto de domínio adicional. O cliente coordena a jornada pelas rotas existentes. Cada serviço conserva seu modelo e armazenamento.

## Fluxo e contratos

1. O front-end envia `{ fullName, email }` a `POST /mkt/leads` pelo gateway.
2. O checkout envia dados sintéticos a `POST /financeiro/clients`. HTTP `201` indica agendamento de `process_payment`, não conclusão síncrona.
3. Ao concluir a simulação, Financeiro publica `client_enrolled` no exchange fanout RabbitMQ com `{ cpf, name, email }`.
4. Marketing consome pela fila `lead_convertion` e converte pelo e-mail; Acadêmico consome pela fila `student_enrollment` e cria o aluno usando nome e e-mail.
5. O gateway encaminha login por `/academico/login` e acesso autenticado a `/academico/cursos`.

`name` no evento do Financeiro e `fullName` no contrato HTTP do Marketing pertencem a fronteiras diferentes. A entrega de produto é o acesso acadêmico; não há adquirente nem pagamento real.

## DDD pragmática

- Usar os três contextos para manter linguagem, estado e regras locais; não criar modelo compartilhado de lead, pagamento e aluno.
- Manter camadas onde já ajudam: Marketing organiza domínio/aplicação/infraestrutura/UI; Financeiro separa domínio, aplicação e infraestrutura; Acadêmico organiza `Domain`, `Application` e `Infrastructure`.
- No Acadêmico, `Student` representa os dados do aluno; `EnrollStudent` coordena matrícula e portas de persistência/notificação; RabbitMQ, RedBean e Symfony Mailer ficam nos adaptadores existentes.
- Preferir contratos explícitos e mudanças pequenas a abstrações genéricas. Não introduzir serviço, API, evento, schema ou regra sem critério de aceite.

## Executar e verificar localmente

Na raiz da stack local, com Docker Compose e portas necessárias disponíveis:

```powershell
docker compose up --build
```

O Compose declara volumes nomeados para MongoDB, RabbitMQ e Postgres. Nesta máquina, o esgotamento do pool padrão exige que a rede `default` use `JORNADA_DOCKER_SUBNET`, cujo padrão é `10.254.240.0/24`; essa faixa foi validada localmente e pode precisar ser substituída em outra máquina para evitar conflito com redes ou rotas existentes. A stack anterior foi somente parada com `docker compose stop`, mantendo containers e dados. Os dados da stack antiga não foram migrados para os novos volumes nomeados.

Em outro PowerShell 5.1, com a stack ativa:

```powershell
powershell -NoProfile -File .\scripts\smoke.ps1
```

O smoke cria dados sintéticos, espera HTTP `201` em Marketing e Financeiro e tenta login Acadêmico até receber HTTP `200` com JWT. Ele deixa lead no MongoDB e aluno no Postgres; não limpa esses registros. A senha de demonstração atual é `123456`. O `MAILER_DSN=null://null` padrão não entrega e-mail.

Validação local executada nesta sessão: Marketing `201`, Financeiro `201`, login Acadêmico `200/JWT`, lead com `status=1` no MongoDB, aluno no Postgres com hash Argon2ID, consumer ativo e PSR-12 aprovado. Este registro não comprova entrega de e-mail nem constitui execução de testes nesta edição documental.

No monorepo limpo, o smoke repetido passou localmente em 2026-10-18, após o ajuste do timeout: Marketing `201`, Financeiro `201` e login Acadêmico `200/JWT`. Isso não comprova entrega de e-mail.

## Pendências e limites reais

- A procedência e a declaração de permissão para publicação estão em [ORIGIN.md](ORIGIN.md).
- O consumer Acadêmico usa `no_ack: true`; o publicador não marca a mensagem como persistente. Retry, idempotência e recuperação de falhas não estão cobertos por esta arquitetura.
- O smoke não prova envio de e-mail. A credencial fixa de demonstração e o pagamento simulado não são adequados a produção.
- A stack conserva versões antigas de Node e PHP; Compose local não implica prontidão operacional para produção.
