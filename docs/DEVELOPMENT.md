# Desenvolvimento — fluxo leve de SDD

Este fluxo mantém as decisões visíveis e cada mudança pequena o bastante para ser revisada. As specs ficam em `docs/specs/`; o formato não exige ferramenta ou framework adicional.

## Ciclo de trabalho

1. **Entender:** inspecionar o fluxo e os contratos existentes; registrar fatos, hipóteses e lacunas separadamente.
2. **Especificar:** escrever contexto, objetivo, limites de domínio, contratos preservados, critérios de aceite verificáveis e pendências.
3. **Aprovar:** não iniciar implementação até o responsável aprovar o escopo e as decisões que bloqueiam o trabalho.
4. **Implementar:** executar uma fatia por vez, somente nos arquivos autorizados; preservar alterações locais e de outros colaboradores.
5. **Verificar:** rodar os testes proporcionais ao escopo e guardar evidência do resultado, distinguindo teste executado de resultado informado por outra pessoa.
6. **Revisar e fechar:** comparar diff e critérios de aceite, conferir atribuição/contratos, documentar pendências e resumir o que foi verificado.

## Papéis reutilizáveis

| Papel | Responsabilidade | Limite de atuação |
|---|---|---|
| **Planejador** | Investiga o mínimo necessário, propõe limites e decisões, escreve spec curta com critérios verificáveis e riscos. | Não implementa antes da aprovação; não inventa APIs, eventos ou regras de negócio. |
| **Programador** | Implementa a spec aprovada em mudanças pequenas, preserva contratos e outros trabalhos no repositório, executa verificações pertinentes. | Não amplia escopo nem reverte edições alheias sem autorização. Registra desvios e bloqueios. |
| **Revisor** | Confere o diff contra a spec, limites DDD, contratos, critérios de aceite, testes e atribuição de origem. | Não substitui a aprovação de produto nem declara testes que não executou; findings apontam evidência e impacto. |

Uma pessoa pode assumir mais de um papel em etapas diferentes; a separação de responsabilidades e a evidência continuam explícitas.

## Spec mínima

Para uma mudança pequena, registrar:

- contexto e resultado esperado;
- serviços/contextos envolvidos e responsabilidades que permanecem fora do escopo;
- contratos de entrada, saída e eventos a preservar;
- critérios de aceite observáveis e teste local adequado;
- riscos, dependências e decisões que precisam de resposta.

Uma spec deve guiar um incremento delimitado, não prometer arquitetura futura como se estivesse implementada. Atualizar status apenas com evidência: por exemplo, “implementação local validada parcialmente” quando parte dos critérios foi confirmada e os limites restantes continuam nomeados.

## Verificações para esta jornada

O teste local de ponta a ponta disponível é `powershell -NoProfile -File .\scripts\smoke.ps1`, com a stack Compose ativa. Usa dados fictícios, deixa registros no MongoDB/Postgres e verifica as respostas HTTP e o JWT; não demonstra entrega de e-mail nem substitui verificação direta da conversão do lead. O padrão `MAILER_DSN=null://null` é um transporte nulo.

Em revisão, não confundir `201` de agendamento com pagamento ou entrega concluídos. Separar resultado observado, resultado reportado e comportamento ainda não exercitado. Toda publicação independente depende de esclarecimento de permissões e da atribuição mantida em [ORIGIN.md](ORIGIN.md).
