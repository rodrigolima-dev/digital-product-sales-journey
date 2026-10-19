# 002 — Caso de uso de matrícula no consumer Acadêmico

**Status:** implementação local validada parcialmente; repositório único consolidado; permissões para publicação ainda pendentes.

## Objetivo e contexto

Separar a matrícula recebida pelo consumer Acadêmico em uma fatia pequena e legível: entrada RabbitMQ, caso de uso, domínio `Student`, portas para persistência/notificação e adaptadores com as tecnologias atuais. Não alterar serviços, APIs ou fluxo comercial.

A organização atual é um único repositório com cinco pastas normais (`front-end`, `mkt-node`, `financeiro-php`, `academico-php` e `academico-php-web`). Esta spec descreve o snapshot consolidado; a procedência está registrada em `docs/ORIGIN.md`.

## Estrutura pretendida

- `receive.php` funciona como bootstrap/composition root do consumer. Um adaptador de entrada RabbitMQ declara e consome a fila, decodifica a mensagem e chama o caso de uso; não contém regra de matrícula nem acesso direto a `R` ou Symfony Mailer.
- Um caso de uso de aplicação (`EnrollStudent`) coordena a criação do aluno, persistência e notificação, na ordem atual.
- `Student` no domínio representa somente os dados já usados: nome, e-mail e hash da senha. Não introduzir regras, estados ou atributos sem requisito existente.
- Portas explícitas isolam persistência (`StudentRepository`) e notificação (`EnrollmentNotifier`). Adaptadores de infraestrutura implementam as portas usando RedBeanPHP e Symfony Mailer já presentes.
- O namespace Composer de `src/` é `RodrigoLima\\Academico\\`; os imports foram atualizados e o autoload Composer foi validado nesta versão.

## Contratos e comportamento preservados

- Exchange RabbitMQ `client_enrolled` (fanout, durável), fila `student_enrollment` (durável), binding, consumo `no_ack: true` e payload `{ name, email, cpf }` permanecem iguais. O Acadêmico usa `name` e `email`; aceita `cpf` como hoje, sem passar a persistir esse campo.
- Persistir na mesma conexão Postgres e tabela `students`, com colunas existentes `name`, `email` e `password`; não alterar migrações, banco ou contrato de login.
- Manter senha `123456` e hash Argon2ID exatamente como hoje. A senha persistida continua sendo hash; a notificação mantém o comportamento visível e os dados de acesso atuais.
- Persistência ocorre antes da notificação. Manter remetente (`MAIL_FROM`), transporte (`MAILER_DSN`), destinatário, assunto/conteúdo do e-mail e log `Matrícula processada` após a notificação concluir.
- Sem nova fila/evento, endpoint, serviço, coluna, regra de retry/ack ou alteração de confirmação. Falha de notificação não desfaz a gravação já realizada.

## Critérios de aceite

1. O adaptador recebe `client_enrolled` na fila existente e delega ao caso de uso sem executar SQL/RedBean ou construir e enviar e-mail diretamente.
2. Para mensagem sintética válida, o caso de uso constrói `Student`, gera o mesmo hash Argon2ID da senha existente, persiste pela porta e solicita a notificação pela porta, nessa ordem.
3. O adaptador RedBean grava uma linha em `students` com `name` e `email` do evento e `password` em hash; login atual continua reconhecendo o mesmo comportamento de credencial.
4. O adaptador Symfony mantém configuração e conteúdo visíveis da notificação; após conclusão, permanece o log `Matrícula processada`.
5. Payload, exchange, fila, banco/tabela, confirmação visível, `no_ack` e comportamento de falha após persistência não mudam. Nenhum serviço ou dependência novo é introduzido.

## Verificação E2E local

Com a stack local e Postgres, RabbitMQ e consumer ativos, enviar pelo checkout existente um pagamento simulado com dados inteiramente sintéticos; isso deve publicar o evento real `client_enrolled`, sem publicar mensagem manual em endpoint novo. Usar `MAILER_DSN` configurado para um sink de teste acessível. O Compose tem fallback `null://null`: nesse modo validar que a porta/adaptador conclui e o log aparece, sem alegar entrega de e-mail. Confirmar no Postgres uma única linha de teste em `students`, comparar nome/e-mail, verificar que `password` não contém a senha em texto puro e autenticar pela rota de login existente. O consumer deve registrar `Matrícula processada`; nenhum evento ou schema é alterado.
