# Documentação de Regras de Negócio — Piuba Pescados

**Sistema de Gestão Aquícola**

> Este documento descreve todas as regras de negócio do sistema Piuba Pescados, traduzidas para linguagem funcional. Destina-se a treinamento, onboarding e operação do sistema.

---

## Sumário

1. [Tanques](#1-tanques)
2. [Lotes](#2-lotes)
3. [Povoamentos](#3-povoamentos)
4. [Transferências](#4-transferências)
5. [Arraçoamentos](#5-arraçoamentos)
6. [Biometrias](#6-biometrias)
7. [Mortalidades](#7-mortalidades)
8. [Sensores](#8-sensores)
9. [Leituras de Sensores](#9-leituras-de-sensores)
10. [Qualidade da Água](#10-qualidade-da-água)
11. [Controle de Ração](#11-controle-de-ração)
12. [Compras](#12-compras)
13. [Fornecedores](#13-fornecedores)
14. [Financeiro](#14-financeiro)
15. [Categorias Financeiras](#15-categorias-financeiras)
16. [Rateio de Custo Fixo](#16-rateio-de-custo-fixo)
17. [Vendas](#17-vendas)
18. [Clientes](#18-clientes)
19. [Despescas](#19-despescas)
20. [Curva de Crescimento](#20-curva-de-crescimento)
21. [Alertas](#21-alertas)
22. [Assinaturas](#22-assinaturas)
23. [Histórico de Tanques](#23-histórico-de-tanques)
24. [Histórico de Lotes](#24-histórico-de-lotes)

---

## 1. Tanques

### 1.1 Visão Geral

O tanque é a unidade física fundamental do sistema. Representa o espaço onde os peixes são criados — pode ser um tanque-rede, viveiro, raceway ou qualquer outra estrutura de cultivo. Todo o controle operacional da fazenda começa pelo cadastro dos tanques.

### 1.2 Principais Funcionalidades

- Cadastrar novos tanques informando nome, tipo, capacidade e localização
- Consultar todos os tanques da empresa (com paginação)
- Consultar tanques que ainda não possuem lotes ativos
- Alterar dados de um tanque existente
- Desativar (remover) um tanque do sistema
- Consultar os tipos de tanque disponíveis (catálogo fixo do sistema)

### 1.3 Regras de Negócio

| # | Regra |
|---|-------|
| T1 | Todo tanque nasce com o estado **"Ativo"** ao ser cadastrado, independentemente do que for informado |
| T2 | O tanque deve pertencer a uma empresa válida e a um tipo de tanque válido |
| T3 | A capacidade do tanque deve ser de pelo menos **1 litro** |
| T4 | Os tipos de tanque são **somente leitura** — não podem ser criados nem excluídos pelo usuário |
| T5 | Um tanque pode estar em um dos seguintes estados: **Ativo** ou **Inativo** |
| T6 | Quando o tanque passa por manutenção, limpeza ou pousio (via Histórico de Tanques), seu estado é atualizado para refletir essa condição operacional |
| T7 | Toda mudança de estado gera automaticamente um registro no Histórico de Tanques |
| T8 | A remoção de um tanque é lógica (o registro é preservado para fins de histórico, mas não aparece mais nas listagens) |

### 1.4 Fluxo Operacional

1. O gestor acessa o sistema e cadastra um tanque informando nome, tipo, capacidade em litros e localização
2. O tanque é criado automaticamente como "Ativo"
3. Quando necessário, o gestor pode editar informações ou desativar o tanque
4. Para consultar tanques disponíveis para receber novos lotes, utiliza o filtro "tanques sem lotes"

### 1.5 Entradas e Saídas

| Dado informado pelo usuário | Dado gerado pelo sistema |
|-----------------------------|--------------------------|
| Nome do tanque | Identificador único (automático) |
| Tipo de tanque | Estado inicial ("Ativo") |
| Capacidade em litros | Data de criação |
| Localização | Histórico de alterações de estado |

### 1.6 Alertas e Exceções

- Não é possível cadastrar um tanque sem informar a empresa, o tipo e a capacidade
- Se o tipo de tanque informado não existir no catálogo, o cadastro é recusado

### 1.7 Integrações

- **Lotes**: cada tanque pode conter lotes de peixes
- **Sensores**: sensores de monitoramento são instalados nos tanques
- **Qualidade da Água**: medições são associadas a tanques específicos
- **Histórico de Tanques**: toda mudança de estado é registrada
- **Rateio de Custo**: a capacidade do tanque é usada no método de rateio por volume

---

## 2. Lotes

### 2.1 Visão Geral

O lote (ou "batch") representa um grupo de peixes que compartilham a mesma origem e são gerenciados juntos dentro de um tanque. É a unidade central de acompanhamento zootécnico — através do lote, o sistema monitora alimentação, crescimento, mortalidade e desempenho financeiro.

### 2.2 Principais Funcionalidades

- Cadastrar um novo lote de peixes em um tanque
- Consultar e editar informações do lote
- Acompanhar o desempenho biológico e financeiro do lote
- Encerrar (finalizar) o lote ao término do ciclo de produção
- Remover um lote

### 2.3 Regras de Negócio

| # | Regra |
|---|-------|
| L1 | **Cada tanque pode ter no máximo 1 lote ativo por vez.** Se já houver um lote ativo no tanque, o sistema recusa a criação de outro |
| L2 | Todo lote nasce com o estado **"Ativo"** ao ser cadastrado |
| L3 | O lote pode estar em dois estados: **Ativo** ou **Finalizado** |
| L4 | Um lote finalizado **não pode ser reativado** |
| L5 | Ao finalizar o lote, o sistema gera automaticamente um registro de despesca e calcula um relatório completo de encerramento |
| L6 | A quantidade inicial de peixes deve ser de pelo menos **1 unidade** |
| L7 | O sistema de cultivo pode ser **Engorda** ou **Berçário** |
| L8 | O custo unitário do alevino (custo por peixe) é registrado no lote para cálculos financeiros, mas não pode ser alterado pela tela de edição padrão |
| L9 | A remoção do lote é lógica |

### 2.4 Fluxo Operacional — Ciclo de Vida do Lote

1. **Criação**: gestor seleciona um tanque disponível e registra espécie, quantidade inicial, data de entrada e tipo de cultivo
2. **Operação**: durante o ciclo, registra arraçoamentos, biometrias, mortalidades e transferências
3. **Encerramento**: ao finalizar o lote, informa peso total e preço por kg da despesca
4. **Relatório**: o sistema gera automaticamente o relatório de encerramento

### 2.5 Relatório de Encerramento do Lote

Ao finalizar, o sistema calcula automaticamente:

| Indicador | Descrição |
|-----------|-----------|
| Receita Bruta | Peso total × Preço por kg |
| Custo de Alevinos | Quantidade inicial × Custo unitário do alevino |
| Custo de Ração | Total gasto em ração durante o ciclo |
| Lucro | Receita − Custos totais |
| Custo por kg | Custo total ÷ Peso total |
| ROI | (Lucro ÷ Custo total) × 100 |
| Ponto de Equilíbrio | Custo total ÷ Preço por kg |

### 2.6 Indicadores de Desempenho (durante o ciclo)

| Indicador | Cálculo |
|-----------|---------|
| Taxa de Sobrevivência | (Peixes vivos ÷ Quantidade inicial) × 100 |
| População Atual | Quantidade inicial − Total de mortalidades |
| Biomassa Atual | (Peixes vivos × Último peso médio) ÷ 1.000 (em kg) |
| Perda Financeira por Mortalidade | Mortalidades × (Custo alevino + Custo ração por peixe) |
| Custo Total de Ração | Soma de toda ração consumida × preço por kg |

### 2.7 Alertas e Exceções

- Tentativa de criar lote em tanque que já possui lote ativo → operação recusada
- Tentativa de finalizar lote já finalizado → operação recusada
- Não é possível criar lote com quantidade inferior a 1

### 2.8 Integrações

- **Tanques**: cada lote pertence a um tanque
- **Povoamentos**: o lote pode ter um ou mais registros de povoamento (estocagem)
- **Arraçoamentos**: registros de alimentação são vinculados ao lote
- **Biometrias**: medições de crescimento são feitas por lote
- **Mortalidades**: perdas são registradas por lote
- **Transferências**: o lote pode ser movido entre tanques
- **Vendas**: peixes são vendidos a partir de lotes
- **Curva de Crescimento**: pontos de peso médio são registrados por lote

---

## 3. Povoamentos

### 3.1 Visão Geral

O povoamento (ou "estocagem") registra a entrada de peixes em um lote. Cada vez que peixes são adicionados ao lote — seja no início do ciclo ou em reposições — cria-se um registro de povoamento. É a base para cálculos de biomassa, custos acumulados e controle de estoque vivo.

### 3.2 Principais Funcionalidades

- Registrar um novo povoamento para um lote
- Consultar povoamentos de um lote
- Editar informações de um povoamento
- Remover um povoamento

### 3.3 Regras de Negócio

| # | Regra |
|---|-------|
| P1 | Todo povoamento nasce com estado **"Ativo"** |
| P2 | O povoamento pode ser **Ativo** ou **Encerrado** |
| P3 | Um povoamento encerrado **não pode receber novos registros** de histórico manual |
| P4 | O encerramento do povoamento acontece automaticamente quando uma **venda com despesca total** é realizada |
| P5 | A quantidade de peixes deve ser de pelo menos **1** |
| P6 | O peso médio deve ser maior ou igual a **zero** |
| P7 | A **quantidade atual** e a **biomassa estimada** são calculadas automaticamente na criação, com base na quantidade e peso médio informados |
| P8 | O custo fixo acumulado inicia em zero e é incrementado pelo módulo de Rateio de Custo |

### 3.4 Cálculos Automáticos

| Campo | Fórmula |
|-------|---------|
| Biomassa Inicial | Quantidade × Peso médio |
| Quantidade Atual (início) | Igual à quantidade informada |
| Biomassa Estimada (início) | Igual à biomassa inicial |
| Custo Unitário Atual | Custo fixo acumulado ÷ (Biomassa inicial − Peso já vendido) |

### 3.5 Entradas e Saídas

| Informado pelo usuário | Gerado pelo sistema |
|------------------------|---------------------|
| Lote de destino | Identificador único |
| Data do povoamento | Quantidade atual |
| Quantidade de peixes | Biomassa estimada |
| Peso médio | Custo fixo acumulado |
| | Estado (Ativo/Encerrado) |

### 3.6 Integrações

- **Lotes**: todo povoamento pertence a um lote
- **Rateio de Custo**: os custos fixos são distribuídos entre os povoamentos ativos
- **Vendas**: a venda referencia um povoamento específico para controle de biomassa
- **Histórico de Lotes**: eventos são registrados no histórico do povoamento

---

## 4. Transferências

### 4.1 Visão Geral

A transferência permite mover um lote de peixes de um tanque para outro. É uma operação logística comum quando, por exemplo, peixes precisam ser transferidos para tanques maiores conforme crescem, ou quando um tanque precisa de manutenção.

### 4.2 Principais Funcionalidades

- Registrar uma transferência de lote entre tanques
- Consultar transferências realizadas
- Editar uma transferência
- Cancelar (excluir) uma transferência, revertendo o lote para o tanque original

### 4.3 Regras de Negócio

| # | Regra |
|---|-------|
| TR1 | O tanque de **origem** e o tanque de **destino** devem ser **diferentes** |
| TR2 | O lote deve estar fisicamente no tanque de **origem** informado (o sistema verifica se o tanque atual do lote corresponde à origem) |
| TR3 | O tanque de **destino** não pode ter **outro lote ativo** (exceto o próprio lote sendo transferido) |
| TR4 | Ao transferir, o sistema move o lote para o tanque de destino e ajusta a quantidade inicial do lote (subtrai a quantidade transferida) |
| TR5 | A quantidade transferida deve ser de pelo menos **1** |
| TR6 | A descrição da transferência é **obrigatória** |

### 4.4 Fluxo Operacional

1. O operador seleciona o lote e informa tanque de origem, tanque de destino, quantidade e motivo
2. O sistema verifica se o lote está realmente no tanque de origem
3. O sistema verifica se o tanque de destino está livre para receber
4. A transferência é registrada, o lote é movido e a quantidade ajustada

### 4.5 Exclusão de Transferência (Reversão)

Ao excluir uma transferência:
- O sistema tenta **reverter** o lote para o tanque de origem
- A quantidade transferida é **somada de volta** ao lote
- Se o tanque de origem já tiver **outro lote ativo**, a exclusão é **bloqueada**

### 4.6 Alertas e Exceções

- Origem igual ao destino → operação recusada
- Lote não está no tanque de origem → operação recusada
- Tanque de destino já tem outro lote ativo → operação recusada
- Reversão impossível (origem já ocupada) → exclusão bloqueada

### 4.7 Integrações

- **Tanques**: origem e destino da transferência
- **Lotes**: o lote que está sendo movido
- **Histórico de Lotes**: o evento "transferência" pode ser registrado manualmente no histórico

---

## 5. Arraçoamentos

### 5.1 Visão Geral

O arraçoamento (ou "alimentação") registra cada fornecimento de ração ao lote de peixes. É um dos registros mais frequentes na rotina de uma fazenda aquícola e afeta diretamente o controle de estoque de ração, os cálculos de conversão alimentar e os custos operacionais.

### 5.2 Principais Funcionalidades

- Registrar fornecimento de ração a um lote
- Consultar histórico de arraçoamentos
- Editar um registro de alimentação
- Remover um registro de alimentação

### 5.3 Regras de Negócio

| # | Regra |
|---|-------|
| A1 | Só é possível registrar alimentação em **lotes ativos** |
| A2 | A quantidade fornecida deve ser **maior que zero** |
| A3 | O tipo de ração é obrigatório (texto livre, até 100 caracteres) |
| A4 | Ao registrar, o sistema **atualiza automaticamente** o controle de ração da empresa (estoque atual, consumo total, média diária) |
| A5 | Se um estoque específico for informado, o sistema **reduz a quantidade** desse estoque pelo valor de redução informado |
| A6 | Ao **editar** um arraçoamento, o efeito anterior no estoque é **revertido** e o novo efeito é aplicado |
| A7 | Ao **excluir** um arraçoamento, o estoque **não é revertido** automaticamente (apenas a exclusão lógica do registro) |
| A8 | Após cada registro, o sistema verifica se há **desvio de ração** em relação à recomendação da última biometria |
| A9 | O evento de alimentação gera automaticamente um registro no **Histórico de Lotes** (povoamento) |

### 5.4 Verificação de Desvio de Ração

Após cada arraçoamento, o sistema compara:
- **Quantidade fornecida** vs. **Ração recomendada** (calculada na última biometria)
- Se o desvio for **maior que 20%**, um **alerta** é gerado automaticamente

### 5.5 Entradas e Saídas

| Informado pelo usuário | Gerado pelo sistema |
|------------------------|---------------------|
| Lote de destino | Atualização do controle de ração |
| Data da alimentação | Redução do estoque (se aplicável) |
| Quantidade fornecida (kg) | Registro no histórico do povoamento |
| Tipo de ração | Alerta de desvio (se aplicável) |
| Estoque de origem (opcional) | Média de consumo diário |
| Quantidade a reduzir do estoque | |

### 5.6 Integrações

- **Lotes**: o arraçoamento é vinculado a um lote ativo
- **Controle de Ração**: atualiza estoque agregado por tipo de ração
- **Estoques de Insumos**: pode reduzir um estoque específico
- **Biometrias**: a recomendação de ração vem da biometria
- **Alertas**: gera alertas de desvio de ração
- **Histórico de Lotes**: gera registro automático

---

## 6. Biometrias

### 6.1 Visão Geral

A biometria é a medição periódica do crescimento dos peixes. É fundamental para avaliar o desempenho do lote, calcular a conversão alimentar (FCR), estimar a biomassa atual e ajustar a quantidade de ração recomendada.

### 6.2 Principais Funcionalidades

- Registrar biometria de um lote (por amostragem ou peso médio direto)
- Consultar histórico de biometrias
- Editar uma biometria existente
- Remover uma biometria

### 6.3 Regras de Negócio

| # | Regra |
|---|-------|
| B1 | Só é possível registrar biometria em **lotes ativos** |
| B2 | O peso médio deve ser **maior que zero** (após cálculo) |
| B3 | O lote precisa ter **pelo menos um arraçoamento** registrado antes de fazer biometria |
| B4 | **Não é permitido** registrar duas biometrias na **mesma data** para o mesmo lote |
| B5 | O peso médio pode ser informado diretamente **OU** calculado a partir de uma amostra (peso da amostra ÷ quantidade de peixes amostrados) |
| B6 | A **biomassa estimada** é calculada automaticamente: peso médio × quantidade inicial do lote |
| B7 | A **conversão alimentar (FCR)** é calculada automaticamente com base no período desde a biometria anterior |
| B8 | A **densidade** do tanque é calculada: biomassa ÷ capacidade do tanque (em litros) |
| B9 | A **ração diária recomendada** é calculada com base no peso médio dos peixes |
| B10 | Cada biometria adiciona automaticamente um ponto na **Curva de Crescimento** do lote |
| B11 | Após o registro, o sistema verifica alertas de **densidade alta** e **FCR elevado** |

### 6.4 Cálculo da Conversão Alimentar (FCR)

| Componente | Descrição |
|------------|-----------|
| Período | Data da biometria anterior (ou entrada do lote) até a data atual |
| Ração no período | Soma de toda ração reduzida do estoque no período |
| Ganho de biomassa | (Peso atual − Peso anterior) × Peixes vivos ÷ 1.000 (em kg) |
| **FCR** | **Ração no período ÷ Ganho de biomassa** |

### 6.5 Tabela de Ração Recomendada

| Peso médio dos peixes | % do peso corporal (ração diária) |
|-----------------------|-----------------------------------|
| Menos de 10g | 10% |
| 10g a 50g | 6% |
| 50g a 150g | 4% |
| 150g a 400g | 3% |
| 400g a 800g | 2% |
| Acima de 800g | 1,5% |

A ração diária = Biomassa total em kg × Percentual da faixa

### 6.6 Alertas Automáticos

- **FCR > 2.0** → Alerta de "FCR elevado"
- **Densidade > 50 kg/m³** → Alerta de "Densidade alta"

### 6.7 Integrações

- **Lotes**: biometria é feita por lote
- **Arraçoamentos**: ração consumida é usada no cálculo do FCR
- **Mortalidades**: peixes mortos são subtraídos para cálculo de biomassa
- **Curva de Crescimento**: cada biometria gera um ponto na curva
- **Alertas**: gera alertas de FCR e densidade
- **Controle de Ração**: a recomendação de ração é usada para verificar desvios

---

## 7. Mortalidades

### 7.1 Visão Geral

O módulo de mortalidade registra as perdas de peixes em cada lote. É essencial para monitorar a saúde do plantel, calcular a taxa de sobrevivência e avaliar impactos financeiros.

### 7.2 Principais Funcionalidades

- Registrar mortalidade em um lote (quantidade e causa)
- Consultar histórico de mortalidades
- Editar um registro de mortalidade
- Consultar taxa de sobrevivência do lote
- Remover um registro

### 7.3 Regras de Negócio

| # | Regra |
|---|-------|
| M1 | Só é possível registrar mortalidade em **lotes ativos** |
| M2 | A quantidade de mortos deve ser de pelo menos **1** |
| M3 | A **soma de todas as mortalidades** do lote não pode exceder a quantidade de peixes vivos (quantidade inicial − mortalidades anteriores) |
| M4 | A causa da mortalidade é **obrigatória** |
| M5 | A data da mortalidade é **obrigatória** |
| M6 | Ao editar, a validação de quantidade exclui o próprio registro do cálculo (permite corrigir sem falso conflito) |
| M7 | O evento de mortalidade gera automaticamente um registro no **Histórico de Lotes** (povoamento) |
| M8 | Se a taxa de mortalidade acumulada ultrapassar **10% da população inicial**, um **alerta crítico** é gerado |

### 7.4 Cálculos

| Indicador | Fórmula |
|-----------|---------|
| Taxa de Sobrevivência | (Peixes vivos ÷ Quantidade inicial) × 100 |
| Taxa de Mortalidade | (Total de mortos ÷ Quantidade inicial) × 100 |
| Perda Financeira Estimada | Mortalidades × (Custo do alevino + Custo médio de ração por peixe) |

### 7.5 Alertas e Exceções

- Quantidade de mortos excede o número de peixes vivos → operação recusada
- Taxa de mortalidade > 10% → alerta de "Mortalidade Crítica"

### 7.6 Integrações

- **Lotes**: mortalidade é registrada por lote
- **Biometrias**: mortalidades afetam o cálculo de FCR (peixes vivos)
- **Alertas**: gera alerta se taxa ultrapassa 10%
- **Histórico de Lotes**: gera registro automático no histórico do povoamento
- **Desempenho do Lote**: impacta taxa de sobrevivência e indicadores financeiros

---

## 8. Sensores

### 8.1 Visão Geral

O módulo de sensores permite cadastrar e gerenciar os equipamentos de monitoramento instalados nos tanques. Cada sensor é vinculado a um tanque e pode medir parâmetros como pH, temperatura, oxigênio ou amônia.

### 8.2 Principais Funcionalidades

- Cadastrar sensores nos tanques
- Consultar sensores por tanque, tipo ou estado
- Editar informações do sensor
- Alterar o estado operacional do sensor
- Remover um sensor

### 8.3 Regras de Negócio

| # | Regra |
|---|-------|
| S1 | Cada sensor é vinculado a **um tanque** e a **uma empresa** |
| S2 | Os tipos de sensor disponíveis são: **pH**, **Temperatura**, **Oxigênio** e **Amônia** |
| S3 | O sensor pode estar em três estados: **Ativo**, **Inativo** ou **Em Manutenção** |
| S4 | A data de instalação é **obrigatória** |
| S5 | Sensores com estado "Inativo" ou "Em Manutenção" aparecem como **alertas** no painel da empresa |

### 8.4 Entradas e Saídas

| Informado pelo usuário | Gerado pelo sistema |
|------------------------|---------------------|
| Tanque de instalação | Identificador único |
| Tipo de sensor | Alertas no painel (se inativo/manutenção) |
| Data de instalação | |
| Estado operacional | |
| Observações | |

### 8.5 Integrações

- **Tanques**: sensor é instalado em um tanque
- **Leituras de Sensores**: cada leitura é vinculada a um sensor
- **Painel (Dashboard)**: sensores inativos/manutenção geram alertas

---

## 9. Leituras de Sensores

### 9.1 Visão Geral

Cada leitura registra um valor medido por um sensor em um determinado momento. São os dados brutos que alimentam o monitoramento contínuo dos tanques.

### 9.2 Principais Funcionalidades

- Registrar uma leitura de sensor
- Consultar leituras por sensor, tanque ou período
- Editar uma leitura
- Remover uma leitura

### 9.3 Regras de Negócio

| # | Regra |
|---|-------|
| LR1 | Cada leitura deve estar vinculada a um **sensor existente** |
| LR2 | O valor medido é **obrigatório** (numérico) |
| LR3 | A unidade de medida é **obrigatória** (texto livre, ex.: °C, mg/L, pH) |
| LR4 | A data/hora da medição é **obrigatória** |
| LR5 | Atualmente, as leituras são registradas **via API** (manual ou por sistema externo). Não há ingestão automática de dados de dispositivos IoT implementada |

### 9.4 Entradas e Saídas

| Informado pelo usuário/sistema | Gerado pelo sistema |
|-------------------------------|---------------------|
| Sensor de origem | Identificador único |
| Valor medido | Contagem de leituras (Dashboard) |
| Unidade de medida | |
| Data/hora da medição | |
| Observações | |

### 9.5 Integrações

- **Sensores**: leitura pertence a um sensor
- **Painel (Dashboard)**: número de leituras nas últimas 24h é exibido no resumo

---

## 10. Qualidade da Água

### 10.1 Visão Geral

O módulo de qualidade da água registra medições consolidadas dos parâmetros físico-químicos de cada tanque. Diferente das leituras individuais de sensores, aqui são registrados todos os parâmetros de uma vez — formando um "retrato" da qualidade da água naquele momento.

### 10.2 Principais Funcionalidades

- Registrar medição de qualidade da água para um tanque
- Consultar histórico de medições por tanque ou período
- Editar uma medição
- Remover uma medição
- Visualizar tendências (gráficos) por parâmetro e período

### 10.3 Regras de Negócio

| # | Regra |
|---|-------|
| QA1 | A medição deve ser vinculada a um **tanque existente** |
| QA2 | A data/hora da medição é **obrigatória** |
| QA3 | Todos os parâmetros são **opcionais** (pode-se registrar apenas os que foram medidos) |
| QA4 | O **pH** deve estar entre **0 e 14** |
| QA5 | A **temperatura** deve estar entre **-10°C e 50°C** |
| QA6 | Os demais parâmetros (oxigênio, amônia, salinidade, turbidez) devem ser **≥ 0** |
| QA7 | O **tanque não pode ser alterado** após o registro |

### 10.4 Parâmetros Monitorados

| Parâmetro | Unidade | Faixa Ideal | Condição de Alerta |
|-----------|---------|-------------|---------------------|
| pH | - | 6,5 – 8,5 | Fora da faixa |
| Oxigênio Dissolvido | mg/L | ≥ 5,0 | **Abaixo de 5,0** (crítico) |
| Temperatura | °C | 20 – 32 | Fora da faixa |
| Amônia | mg/L | ≤ 0,1 | **Acima de 0,1** (crítico) |
| Salinidade | ppt | — | Sem alerta automático |
| Turbidez | NTU | — | Sem alerta automático |

### 10.5 Níveis de Severidade dos Alertas

| Nível | Condição |
|-------|----------|
| **Crítico** | Amônia ou oxigênio dissolvido fora dos limites |
| **Atenção** | pH ou temperatura fora dos limites |
| **Informativo** | Problemas de sensor ou estoque baixo |

### 10.6 Tendências (Gráficos)

O sistema permite visualizar tendências de qualquer parâmetro com:
- **Períodos**: últimas 24 horas, 7 dias ou 30 dias
- **Granularidade**: por hora ou por dia
- **Dados**: média, mínimo e máximo do parâmetro no período

### 10.7 Integrações

- **Tanques**: medição vinculada a um tanque
- **Painel (Dashboard)**: alertas de qualidade da água são exibidos por tanque
- **Alertas**: parâmetros fora dos limites geram alertas no painel

---

## 11. Controle de Ração

### 11.1 Visão Geral

O controle de ração (inventário de ração) gerencia o estoque agregado de cada tipo de ração na empresa. É atualizado automaticamente a cada registro de arraçoamento e permite monitorar o consumo e o nível de estoque.

### 11.2 Principais Funcionalidades

- Cadastrar tipos de ração com estoque inicial e estoque mínimo
- Consultar estoque atual por tipo de ração
- Editar configurações (estoque mínimo, consumo diário)
- Acompanhar consumo total e média diária

### 11.3 Regras de Negócio

| # | Regra |
|---|-------|
| CR1 | Cada registro de controle é por **empresa + tipo de ração** |
| CR2 | Os valores de estoque, consumo e mínimos devem ser **≥ 0** |
| CR3 | O estoque é **atualizado automaticamente** a cada arraçoamento registrado |
| CR4 | A **média de consumo diário** é recalculada com base nos arraçoamentos realizados (média dos totais diários) |
| CR5 | Quando o estoque atual fica abaixo do **estoque mínimo**, o tanque aparece com alerta de "Estoque baixo" no painel |

### 11.4 Fluxo de Atualização

1. Operador registra um arraçoamento com tipo de ração e quantidade
2. O sistema localiza o controle de ração da empresa para aquele tipo
3. Subtrai a quantidade consumida do estoque atual
4. Soma ao consumo total acumulado
5. Recalcula a média de consumo diário

### 11.5 Integrações

- **Arraçoamentos**: atualizações automáticas a cada alimentação
- **Estoques de Insumos**: quando o arraçoamento referencia um estoque específico, ambos os sistemas são atualizados
- **Painel (Dashboard)**: estoque abaixo do mínimo gera alerta

---

## 12. Compras

### 12.1 Visão Geral

O módulo de compras gerencia as aquisições de insumos (ração, medicamentos, etc.) junto aos fornecedores. Cada compra possui itens detalhados e, ao ser recebida, atualiza automaticamente os estoques.

### 12.2 Principais Funcionalidades

- Registrar uma compra com múltiplos itens
- Consultar compras por status, fornecedor ou período
- Editar uma compra
- Marcar uma compra como "recebida"
- Excluir uma compra (com restrições)

### 12.3 Regras de Negócio

| # | Regra |
|---|-------|
| C1 | Toda compra deve ter pelo menos **1 item** |
| C2 | Cada item deve ter quantidade **> 0** e preço unitário **≥ 0** |
| C3 | O **valor total do item** é calculado automaticamente: Quantidade × Preço unitário |
| C4 | O **valor total da compra** é a soma dos valores dos itens |
| C5 | Uma compra pode estar nos seguintes estados: **Rascunho**, **Confirmada**, **Recebida** ou **Cancelada** |

### 12.4 Máquina de Estados das Compras

```
Rascunho ──→ Confirmada ──→ Recebida
    │              │
    └──→ Cancelada ←┘
```

| Transição | Efeito |
|-----------|--------|
| Rascunho → Confirmada | Nenhum efeito no estoque |
| Confirmada → Recebida | **Estoque é atualizado**: cada item gera entrada no estoque do insumo correspondente |
| Rascunho → Cancelada | Nenhum efeito |
| Confirmada → Cancelada | Nenhum efeito |
| Recebida → (qualquer) | **Não permitido** |
| Cancelada → (qualquer) | **Não permitido** |

### 12.5 Regras de Exclusão

- Compras com status **"Recebida"** **não podem ser excluídas** (os estoques já foram afetados)

### 12.6 Entrada no Estoque (ao receber)

Para cada item da compra:
1. O sistema localiza ou cria o estoque do insumo
2. Incrementa a quantidade disponível
3. Atualiza o preço unitário com o valor da compra
4. Registra uma movimentação de estoque (entrada, referência: compra)

### 12.7 Integrações

- **Fornecedores**: toda compra é vinculada a um fornecedor
- **Insumos/Estoques**: o recebimento atualiza os estoques
- **Movimentações de Estoque**: cada entrada gera registro de rastreabilidade

---

## 13. Fornecedores

### 13.1 Visão Geral

O módulo de fornecedores gerencia o cadastro dos parceiros comerciais que fornecem insumos à fazenda. Cada fornecedor pertence a uma empresa e pode ser vinculado a compras e estoques.

### 13.2 Principais Funcionalidades

- Cadastrar fornecedores com dados de contato
- Consultar, editar e remover fornecedores

### 13.3 Regras de Negócio

| # | Regra |
|---|-------|
| F1 | Nome, contato, telefone e e-mail são **obrigatórios** no cadastro |
| F2 | Cada fornecedor pertence a uma **empresa** |
| F3 | A remoção é **lógica** (preserva o histórico) |

### 13.4 Entradas e Saídas

| Informado pelo usuário | Gerado pelo sistema |
|------------------------|---------------------|
| Nome | Identificador único |
| Contato | |
| Telefone | |
| E-mail | |

### 13.5 Integrações

- **Compras**: fornecedor é vinculado a cada compra
- **Estoques**: estoques podem ser associados a um fornecedor de origem

---

## 14. Financeiro

### 14.1 Visão Geral

O módulo financeiro registra todas as movimentações monetárias da empresa — receitas, despesas e investimentos. Cada transação é classificada em uma categoria e pode estar em diferentes estados de pagamento.

### 14.2 Principais Funcionalidades

- Registrar transações financeiras (receitas, despesas, investimentos)
- Consultar transações por tipo, status, categoria ou período
- Editar transações
- Acompanhar estado de pagamento
- Remover transações

### 14.3 Regras de Negócio

| # | Regra |
|---|-------|
| FN1 | Toda transação deve pertencer a uma **categoria financeira** |
| FN2 | O **tipo** da transação (receita/despesa/investimento) deve coincidir com o tipo da categoria escolhida. Não é possível classificar uma despesa em uma categoria de receita |
| FN3 | O valor da transação deve ser de pelo menos **R$ 0,01** |
| FN4 | A data de vencimento é **obrigatória** |
| FN5 | A data de pagamento **só pode ser informada** quando o status for "Pago" |
| FN6 | A data de pagamento **não pode ser no futuro** |
| FN7 | Se o status for "Pago" e nenhuma data de pagamento for informada, o sistema usa a **data atual** |
| FN8 | Transações geradas automaticamente por outros módulos (ex.: vendas) **não podem ter o valor alterado** |

### 14.4 Estados das Transações

| Estado | Descrição |
|--------|-----------|
| Pendente | Aguardando pagamento/recebimento |
| Pago | Pagamento confirmado |
| Atrasado | Vencimento ultrapassado sem pagamento |
| Cancelado | Transação anulada |

### 14.5 Tipos de Transação

| Tipo | Descrição |
|------|-----------|
| Receita | Entrada de recursos (vendas, recebíveis) |
| Despesa | Saída de recursos (compras, custos operacionais) |
| Investimento | Aplicação de capital (equipamentos, infraestrutura) |

### 14.6 Transações Geradas Automaticamente

| Origem | Tipo | Comportamento |
|--------|------|---------------|
| Venda | Receita | Cria "Conta a Receber" com status Pendente, vinculada à venda |

### 14.7 Integrações

- **Categorias Financeiras**: toda transação pertence a uma categoria
- **Vendas**: vendas podem gerar recebíveis automaticamente
- **Rateio de Custo**: despesas podem ser rateadas entre povoamentos
- **Clientes**: transações vinculadas a vendas são usadas no controle de crédito e inadimplência

---

## 15. Categorias Financeiras

### 15.1 Visão Geral

As categorias financeiras organizam as transações em grupos lógicos, facilitando a classificação e análise. Cada empresa possui suas próprias categorias.

### 15.2 Principais Funcionalidades

- Criar categorias para receitas, despesas ou investimentos
- Listar e editar categorias
- Ativar/desativar categorias
- Excluir categorias (com restrições)

### 15.3 Regras de Negócio

| # | Regra |
|---|-------|
| CF1 | Cada categoria pertence a uma **empresa** |
| CF2 | O nome pode ter até **100 caracteres** |
| CF3 | Cada categoria tem um **tipo** (receita, despesa ou investimento) |
| CF4 | Categorias podem ser **ativadas** ou **desativadas** |
| CF5 | Categorias **com transações vinculadas** não podem ser excluídas |
| CF6 | A organização é **plana** (não há subcategorias/hierarquia) |

### 15.4 Fluxo de Desativação

- Uma categoria desativada **continua existindo** no sistema
- Transações já classificadas **não são afetadas**
- Categorias desativadas podem ser **reativadas** a qualquer momento

### 15.5 Integrações

- **Financeiro**: transações são classificadas por categoria
- **Vendas**: a categoria pode ser vinculada à venda para gerar recebíveis

---

## 16. Rateio de Custo Fixo

### 16.1 Visão Geral

O rateio de custo fixo distribui uma despesa entre os povoamentos ativos da empresa, proporcionalmente a um critério escolhido. É utilizado para alocar custos indiretos (como energia, mão de obra, aluguel) ao custo de produção de cada povoamento.

### 16.2 Principais Funcionalidades

- Criar um rateio de custo sobre uma transação financeira
- Escolher o método de distribuição
- Selecionar os povoamentos que receberão o rateio
- Consultar rateios existentes
- Excluir um rateio (revertendo os custos)

### 16.3 Regras de Negócio

| # | Regra |
|---|-------|
| RC1 | Somente transações do tipo **Despesa** podem ser rateadas |
| RC2 | A transação deve estar com status **Pendente** ou **Pago** |
| RC3 | Cada transação pode ser rateada **apenas uma vez** |
| RC4 | Todos os povoamentos selecionados devem pertencer a **lotes ativos** |
| RC5 | É necessário selecionar pelo menos **1 povoamento** |
| RC6 | O valor rateado é o **valor integral** da transação financeira |
| RC7 | A diferença de centavos no arredondamento é absorvida pelo último item, garantindo que a soma dos rateios seja **exatamente igual** ao valor da transação |
| RC8 | Se a diferença de arredondamento for maior que R$ 0,01, a operação é **recusada** |

### 16.4 Métodos de Distribuição

| Método | Descrição | Exemplo |
|--------|-----------|---------|
| **Igualitário** | Divide igualmente entre todos os povoamentos | R$ 1.000 ÷ 4 povoamentos = R$ 250 cada |
| **Por Biomassa** | Proporcional à biomassa inicial de cada povoamento (quantidade × peso médio) | Povoamento com mais biomassa recebe maior parcela |
| **Por Volume** | Proporcional à capacidade do tanque de cada lote | Tanques maiores recebem maior parcela |

### 16.5 Efeitos do Rateio

- O valor de cada parcela é **somado ao custo fixo acumulado** do respectivo povoamento
- A transação financeira é marcada como **"rateada"** (não pode ser rateada novamente)
- O custo fixo acumulado é usado no cálculo do **custo unitário** do peixe na venda

### 16.6 Exclusão do Rateio (Estorno)

Ao excluir um rateio:
1. Os valores são **subtraídos** do custo fixo acumulado de cada povoamento (com piso em zero)
2. A transação financeira volta a ficar disponível para novo rateio
3. O rateio e seus itens são removidos

### 16.7 Integrações

- **Financeiro**: o rateio opera sobre transações financeiras (despesas)
- **Povoamentos**: os custos são distribuídos entre povoamentos ativos
- **Vendas**: o custo fixo acumulado no povoamento impacta o custo unitário na despesca

---

## 17. Vendas

### 17.1 Visão Geral

O módulo de vendas registra a comercialização de peixes. Uma venda pode representar uma despesca parcial ou total do povoamento, vinculando cliente, peso, preço e gerando automaticamente movimentações financeiras e de estoque.

### 17.2 Principais Funcionalidades

- Registrar uma venda de peixes
- Consultar vendas por período, cliente ou lote
- Editar uma venda
- Excluir uma venda
- Acompanhar receita total

### 17.3 Regras de Negócio

| # | Regra |
|---|-------|
| V1 | A venda deve informar **cliente**, **lote** e **peso total** (mínimo 0,001 kg) |
| V2 | O preço por kg deve ser **≥ 0** |
| V3 | A **receita total** é calculada automaticamente: Peso total × Preço por kg |
| V4 | Se um **povoamento** for informado, o sistema verifica se há **biomassa disponível** suficiente |
| V5 | A biomassa disponível = Biomassa inicial do povoamento − Peso já vendido (excluindo vendas canceladas) |
| V6 | É possível configurar uma **tolerância** de até 50% sobre a biomassa disponível |
| V7 | Se a venda for marcada como **despesca total**, o povoamento é **encerrado** automaticamente |
| V8 | Se uma **categoria financeira** for informada, o sistema gera automaticamente uma **conta a receber** (transação financeira pendente) |
| V9 | A venda gera automaticamente uma **saída de estoque** (biomassa) com o custo unitário calculado a partir do povoamento |
| V10 | A venda pode estar nos estados: **Pendente**, **Confirmada** ou **Cancelada** |
| V11 | Se o cliente exigir **nota fiscal**, o sistema verifica se o cliente possui CPF/CNPJ e endereço cadastrados |
| V12 | O evento de venda gera automaticamente um registro no **Histórico de Lotes** (tipo "despesca") |

### 17.4 Controle de Biomassa

O sistema protege contra a venda de mais peixe do que existe:

1. Calcula a biomassa inicial do povoamento (quantidade × peso médio no povoamento)
2. Subtrai todo o peso já vendido nesse povoamento (vendas não canceladas)
3. Aplica a tolerância configurada (0-50%)
4. Se o peso da venda exceder o disponível (com tolerância), a operação é **recusada**

### 17.5 Custo Unitário na Saída

Ao registrar a saída de biomassa:
- **Custo unitário** = Custo fixo acumulado do povoamento ÷ (Biomassa inicial − Peso já vendido)
- **Custo total da saída** = Peso vendido × Custo unitário

### 17.6 Fluxo Operacional

1. Operador seleciona o cliente e o lote
2. Informa peso total, preço por kg e se é despesca total
3. O sistema valida biomassa disponível e crédito do cliente
4. A venda é registrada
5. Automaticamente: saída de estoque + conta a receber (se categoria informada) + histórico do povoamento
6. Se despesca total: povoamento é encerrado

### 17.7 Integrações

- **Clientes**: toda venda é vinculada a um cliente (com controle de crédito)
- **Lotes e Povoamentos**: controle de biomassa
- **Financeiro**: geração de contas a receber
- **Movimentações de Estoque**: saída de biomassa com custo
- **Histórico de Lotes**: registro automático de despesca

---

## 18. Clientes

### 18.1 Visão Geral

O módulo de clientes gerencia o cadastro dos compradores de pescado. Inclui controle de crédito, classificação por grupo de preço e conformidade com a legislação de proteção de dados.

### 18.2 Principais Funcionalidades

- Cadastrar clientes (pessoa física ou jurídica)
- Consultar, editar e remover clientes
- Anonimizar dados pessoais (LGPD)
- Acompanhar limite de crédito e inadimplência

### 18.3 Regras de Negócio

| # | Regra |
|---|-------|
| CL1 | O tipo de pessoa é obrigatório: **Pessoa Física** ou **Pessoa Jurídica** |
| CL2 | O documento (CPF/CNPJ) deve ser **único por empresa** |
| CL3 | O formato do documento é validado conforme o tipo de pessoa |
| CL4 | O limite de crédito é **opcional** (quando não informado, o crédito é ilimitado) |
| CL5 | Clientes **com obrigações financeiras pendentes ou atrasadas** não podem ser excluídos |
| CL6 | O sistema marca automaticamente como **inadimplente** clientes que possuem transações financeiras com status "Atrasado" vinculadas a vendas |
| CL7 | Clientes podem ser classificados por grupo de preço: **Atacado**, **Varejo** ou **Consumidor** |

### 18.4 Controle de Crédito

Ao registrar uma venda:
1. O sistema verifica se o cliente tem limite de crédito definido
2. Calcula a **exposição atual**: soma das transações financeiras pendentes/atrasadas vinculadas a vendas do cliente
3. Se (exposição + valor da nova venda) > limite de crédito → **venda bloqueada**

### 18.5 Anonimização (LGPD)

- Permite anonimizar os dados pessoais do cliente (mantém apenas identificador e nome)
- Após anonimização, o registro é marcado como excluído
- O histórico de vendas permanece referenciável

### 18.6 Verificação de Dados Fiscais

Quando uma venda exige nota fiscal:
- O sistema verifica se o cliente possui **CPF/CNPJ** e **endereço** cadastrados
- Se faltarem dados, a venda é **recusada**

### 18.7 Integrações

- **Vendas**: cliente é vinculado a cada venda
- **Financeiro**: transações de vendas são usadas no controle de crédito
- **Inadimplência**: atualização automática do status de inadimplente

---

## 19. Despescas

### 19.1 Visão Geral

A despesca representa a retirada de peixes do tanque para comercialização ou encerramento do ciclo. No sistema, existem dois fluxos que envolvem despesca:

1. **Despesca via Venda**: quando uma venda é registrada com povoamento, representa uma despesca operacional (parcial ou total)
2. **Despesca via Encerramento de Lote**: quando o lote é finalizado, um registro formal de despesca é criado

### 19.2 Principais Funcionalidades

- Registrar uma despesca (vinculada ao lote)
- Consultar despescas realizadas
- Editar dados da despesca
- Encerrar lote com dados de despesca final

### 19.3 Regras de Negócio

| # | Regra |
|---|-------|
| D1 | A despesca deve ser vinculada a um **lote existente** |
| D2 | O peso total e o preço por kg devem ser **≥ 0** |
| D3 | A receita total é **informada** (não calculada automaticamente no registro simples) |
| D4 | A data da despesca é **obrigatória** |

### 19.4 Despesca Total (via Venda)

Quando uma venda é marcada como "despesca total":
1. Todo o peixe restante no povoamento é considerado vendido
2. O povoamento é **encerrado** automaticamente (status "Fechado", com data de encerramento)
3. Novas operações nesse povoamento são **bloqueadas**

### 19.5 Despesca no Encerramento de Lote

Ao finalizar o lote:
1. O gestor informa peso total, preço por kg e data da despesca
2. O sistema cria o registro de despesca
3. O lote é marcado como "Finalizado"
4. O relatório de encerramento é gerado (receita, custos, ROI, etc.)

### 19.6 Integrações

- **Lotes**: despesca é vinculada ao lote
- **Vendas**: vendas com povoamento representam despescas operacionais
- **Povoamentos**: despesca total encerra o povoamento
- **Financeiro**: gera movimentação via venda

---

## 20. Curva de Crescimento

### 20.1 Visão Geral

A curva de crescimento registra a evolução do peso médio dos peixes ao longo do tempo. Cada ponto da curva corresponde a uma biometria realizada, permitindo ao gestor visualizar a trajetória de engorda do lote.

### 20.2 Principais Funcionalidades

- Registrar manualmente um ponto na curva
- Consultar a curva de crescimento de um lote
- Editar ou remover pontos
- Acompanhar a evolução do peso médio

### 20.3 Regras de Negócio

| # | Regra |
|---|-------|
| CC1 | Cada ponto é vinculado a um **lote** |
| CC2 | O peso médio deve ser **≥ 0** |
| CC3 | A cada biometria registrada, o sistema **adiciona automaticamente** um ponto na curva com o peso médio calculado |
| CC4 | Pontos também podem ser adicionados **manualmente** |
| CC5 | O sistema **não realiza projeções** de crescimento (apenas armazena os dados históricos) |

### 20.4 Exemplo de Uso

| Data | Peso Médio (g) | Origem |
|------|----------------|--------|
| 01/03 | 5g | Biometria automática |
| 15/03 | 18g | Biometria automática |
| 01/04 | 45g | Biometria automática |
| 15/04 | 90g | Biometria automática |

### 20.5 Integrações

- **Biometrias**: cada biometria gera automaticamente um ponto
- **Lotes**: a curva pertence a um lote

---

## 21. Alertas

### 21.1 Visão Geral

O módulo de alertas monitora condições críticas e gera avisos automáticos para o gestor. Existem dois tipos de alertas no sistema: **alertas persistidos** (salvos no banco) e **alertas do painel** (calculados em tempo real).

### 21.2 Principais Funcionalidades

- Consultar alertas ativos da empresa
- Registrar alertas manualmente (tipo e mensagem)
- Marcar alertas como resolvidos
- Visualizar alertas consolidados no painel por tanque

### 21.3 Alertas Automáticos Persistidos

Estes alertas são criados automaticamente pelo sistema quando condições críticas são detectadas:

| Tipo de Alerta | Condição de Disparo | Módulo de Origem |
|----------------|---------------------|------------------|
| **FCR Elevado** | Conversão alimentar > 2.0 | Biometria |
| **Densidade Alta** | Densidade > 50 kg/m³ | Biometria |
| **Desvio de Ração** | Quantidade fornecida difere > 20% da recomendação | Arraçoamento |
| **Mortalidade Crítica** | Taxa acumulada > 10% da população inicial | Mortalidade |

### 21.4 Alertas do Painel (Tempo Real)

Calculados a cada consulta do painel, por tanque:

| Tipo | Condição |
|------|----------|
| **Qualidade da Água** | Parâmetros fora dos limites (pH, oxigênio, temperatura, amônia) |
| **Estoque Baixo** | Quantidade atual do insumo < Estoque mínimo |
| **Sensor Inoperante** | Sensor com status "Inativo" ou "Em Manutenção" |

### 21.5 Regras de Negócio

| # | Regra |
|---|-------|
| AL1 | Alertas podem estar **Pendentes** ou **Resolvidos** |
| AL2 | O tipo e a mensagem do alerta são **obrigatórios** |
| AL3 | Alertas do painel não são salvos no banco — são recalculados a cada consulta |
| AL4 | O painel classifica a severidade: **Crítico** (amônia, oxigênio), **Atenção** (pH, temperatura), **Informativo** (sensor, estoque) |

### 21.6 Resumo do Painel (Dashboard)

O painel exibe um resumo consolidado:

| Informação | Descrição |
|------------|-----------|
| Tanques ativos | Quantidade de tanques em operação |
| Leituras (24h) | Total de leituras de sensores nas últimas 24 horas |
| Estoques críticos | Insumos com estoque abaixo do mínimo |
| Sensores com problema | Sensores inativos ou em manutenção |
| Alertas por tanque | Lista de alertas agrupados por tanque com severidade |

### 21.7 Integrações

- **Biometrias**: gera alertas de FCR e densidade
- **Arraçoamentos**: gera alertas de desvio de ração
- **Mortalidades**: gera alertas de mortalidade crítica
- **Qualidade da Água**: alertas de parâmetros fora dos limites
- **Estoques**: alertas de estoque baixo
- **Sensores**: alertas de sensores inoperantes

---

## 22. Assinaturas

### 22.1 Visão Geral

O módulo de assinaturas gerencia os planos de acesso das empresas ao sistema. É administrado pela equipe do sistema (área administrativa).

### 22.2 Principais Funcionalidades

- Criar e gerenciar assinaturas para empresas (área administrativa)
- Consultar assinatura de uma empresa
- Alterar plano ou status

### 22.3 Planos Disponíveis

| Plano | Descrição |
|-------|-----------|
| **Básico** | Acesso às funcionalidades essenciais |
| **Premium** | Funcionalidades avançadas |
| **Empresarial** | Acesso completo |

### 22.4 Regras de Negócio

| # | Regra |
|---|-------|
| AS1 | Cada assinatura pertence a uma **empresa** |
| AS2 | A data de término deve ser **posterior ou igual** à data de início |
| AS3 | A assinatura pode estar **Ativa** ou **Cancelada** |
| AS4 | Somente **administradores do sistema** podem gerenciar assinaturas |
| AS5 | Atualmente, os planos **não impõem restrições funcionais** no sistema (funcionalidades disponíveis são as mesmas para todos os planos) |

### 22.5 Integrações

- **Empresas**: cada empresa tem uma assinatura
- **Autenticação**: o acesso ao sistema é controlado por papéis de usuário, não por plano de assinatura

---

## 23. Histórico de Tanques

### 23.1 Visão Geral

O histórico de tanques registra todos os eventos relevantes que ocorrem em cada tanque — desde manutenções até mudanças de estado. Funciona como um "diário de bordo" do tanque.

### 23.2 Principais Funcionalidades

- Registrar eventos manuais (limpeza, manutenção, pousio)
- Consultar histórico de um tanque por tipo de evento ou período
- Acompanhar a linha do tempo do tanque

### 23.3 Tipos de Eventos

| Evento | Origem | Descrição |
|--------|--------|-----------|
| **Limpeza** | Manual (operador) | Registro de limpeza do tanque |
| **Manutenção** | Manual (operador) | Registro de manutenção realizada |
| **Pousio** | Manual (operador) | Período de descanso do tanque |
| **Mudança de Estado** | Automático (sistema) | Registrado sempre que o estado do tanque é alterado |

### 23.4 Regras de Negócio

| # | Regra |
|---|-------|
| HT1 | O tanque deve pertencer à **mesma empresa** do usuário |
| HT2 | Via interface, só é possível registrar: **Limpeza**, **Manutenção** ou **Pousio** |
| HT3 | O evento de **Mudança de Estado** é gerado **somente pelo sistema** quando o status do tanque é alterado |
| HT4 | Eventos de limpeza, manutenção e pousio **alteram o estado do tanque** para refletir sua condição operacional |
| HT5 | A data do evento é **obrigatória** |
| HT6 | A descrição e o responsável são **opcionais** |

### 23.5 Efeitos no Tanque

Quando um evento operacional é registrado:

| Evento | Efeito no tanque |
|--------|------------------|
| Limpeza | Estado → "Em limpeza" |
| Manutenção | Estado → "Em manutenção" |
| Pousio | Estado → "Em pousio" |

Esses estados indicam que o tanque **não está disponível** para novas alocações de lotes.

### 23.6 Fluxo Operacional

1. Operador seleciona um tanque
2. Registra o tipo de evento (limpeza, manutenção ou pousio) com data
3. O estado do tanque é atualizado automaticamente
4. O evento fica registrado na linha do tempo

### 23.7 Integrações

- **Tanques**: eventos pertencem a tanques; alteram o estado do tanque
- **Lotes**: tanques em manutenção/limpeza/pousio não devem receber novos lotes

---

## 24. Histórico de Lotes

### 24.1 Visão Geral

O histórico de lotes (ou histórico de povoamento) registra todos os eventos significativos que ocorrem em um povoamento ao longo do seu ciclo de vida. Funciona como a "ficha médica" do lote — combinando registros manuais e automáticos.

### 24.2 Principais Funcionalidades

- Registrar eventos manuais (biometria, mortalidade, transferência, medicação)
- Consultar histórico de um povoamento por tipo de evento ou período
- Acompanhar a linha do tempo completa do povoamento

### 24.3 Tipos de Eventos

| Evento | Origem | Efeito no Povoamento |
|--------|--------|----------------------|
| **Biometria** | Manual | Atualiza peso médio e biomassa estimada |
| **Mortalidade** | Manual ou Automático | Reduz quantidade atual e biomassa estimada |
| **Transferência** | Manual | Apenas registro (sem movimentação automática) |
| **Medicação** | Manual | Apenas registro |
| **Alimentação** | Automático (ao registrar arraçoamento) | Apenas registro |
| **Despesca** | Automático (ao registrar venda com povoamento) | Apenas registro |

### 24.4 Regras de Negócio

| # | Regra |
|---|-------|
| HL1 | O povoamento deve estar **ativo** para aceitar novos registros (povoamentos encerrados não aceitam) |
| HL2 | O povoamento deve pertencer à **mesma empresa** do usuário |
| HL3 | Via interface, só é possível registrar: **Biometria**, **Mortalidade**, **Transferência** ou **Medicação** |
| HL4 | Eventos de **Alimentação** e **Despesca** são gerados **somente pelo sistema** (automáticos) |
| HL5 | Para eventos de **Mortalidade** e **Transferência**, a **quantidade** é obrigatória |
| HL6 | Para eventos de **Biometria**, o **peso médio** é obrigatório |
| HL7 | Notas são **opcionais** (até 1.000 caracteres) |

### 24.5 Efeitos no Povoamento

| Evento Manual | Atualização Automática |
|---------------|------------------------|
| **Biometria** | `peso_médio` e `biomassa_estimada` do povoamento são recalculados |
| **Mortalidade** | `quantidade_atual` é reduzida e `biomassa_estimada` recalculada |
| **Transferência** | Nenhuma atualização automática no povoamento |
| **Medicação** | Nenhuma atualização automática no povoamento |

### 24.6 Eventos Automáticos — Detalhamento

**Alimentação** (disparado ao registrar arraçoamento):
- Localiza o povoamento **ativo mais recente** do lote
- Registra evento "alimentação" com notas descritivas (kg fornecidos, tipo de ração)
- Se não houver povoamento ativo, o registro **não é criado**

**Mortalidade** (disparado ao registrar mortalidade):
- Localiza o povoamento ativo mais recente do lote
- Registra evento "mortalidade" com a quantidade de mortos e a causa

**Despesca** (disparado ao registrar venda):
- Só é criado se a venda tiver um povoamento associado
- Registra evento "despesca" com notas sobre peso, preço e receita

### 24.7 Fluxo Operacional

1. Operador seleciona um povoamento ativo
2. Registra o tipo de evento (biometria, mortalidade, transferência ou medicação)
3. Informa dados obrigatórios conforme o tipo de evento
4. O sistema salva o registro e, se aplicável, atualiza o povoamento

### 24.8 Integrações

- **Povoamentos**: eventos pertencem a um povoamento específico
- **Arraçoamentos**: geram eventos automáticos de alimentação
- **Mortalidades**: geram eventos automáticos de mortalidade
- **Vendas**: geram eventos automáticos de despesca
- **Biometrias**: atualizam dados do povoamento via histórico manual

---

## Glossário

| Termo | Definição |
|-------|-----------|
| **Tanque** | Estrutura física onde os peixes são criados |
| **Lote** | Grupo de peixes gerenciados juntos em um tanque |
| **Povoamento** | Registro de entrada de peixes em um lote (estocagem) |
| **Arraçoamento** | Fornecimento de ração aos peixes |
| **Biometria** | Medição do peso e tamanho dos peixes |
| **FCR** | Conversão Alimentar — relação entre ração fornecida e ganho de peso |
| **Biomassa** | Peso total estimado de peixes vivos |
| **Despesca** | Retirada de peixes do tanque para venda ou processamento |
| **Pousio** | Período de descanso do tanque entre ciclos de produção |
| **Rateio** | Distribuição proporcional de custos entre diferentes unidades de produção |
| **Inadimplente** | Cliente com pagamentos em atraso |
| **Conta a Receber** | Valor a ser recebido de um cliente referente a uma venda |

---

## Mapa de Dependências entre Módulos

```
                    ┌─────────────┐
                    │  ASSINATURAS │
                    │  (Empresa)   │
                    └──────┬──────┘
                           │
                    ┌──────▼──────┐
                    │   EMPRESA   │
                    └──────┬──────┘
                           │
              ┌────────────┼────────────┐
              │            │            │
       ┌──────▼──────┐   ...    ┌──────▼──────┐
       │   TANQUES   │         │  FINANCEIRO  │
       └──────┬──────┘         └──────┬──────┘
              │                       │
     ┌────────┼────────┐        ┌─────┼─────┐
     │        │        │        │           │
  Sensores   Qualidade  │    Categorias   Rateio
  Leituras   da Água   │    Financeiras   de Custo
                       │
                ┌──────▼──────┐
                │    LOTES    │
                └──────┬──────┘
                       │
        ┌──────────────┼──────────────┐
        │              │              │
  ┌─────▼─────┐  ┌─────▼─────┐  ┌────▼─────┐
  │POVOAMENTOS│  │TRANSFERÊN.│  │  DESPESCA │
  └─────┬─────┘  └───────────┘  └────┬─────┘
        │                             │
   ┌────┼────┬────────┐         ┌────▼─────┐
   │    │    │        │         │  VENDAS  │
   │    │    │        │         └────┬─────┘
   ▼    ▼    ▼        ▼              │
 Arraç. Biom. Mortal. Curva     ┌────▼─────┐
                    Crescim.    │ CLIENTES │
                                └──────────┘
```

---

## Controle de Acesso

O sistema possui três níveis de acesso:

| Papel | Descrição | Acesso |
|-------|-----------|--------|
| **Administrador do Sistema** | Equipe Piuba | Gestão de empresas e assinaturas |
| **Administrador Master** | Dono da empresa | Acesso total à empresa |
| **Administrador da Empresa** | Gestor operacional | Acesso às funcionalidades operacionais conforme permissões |

Cada funcionalidade possui **permissões** específicas de visualização, criação, edição e exclusão, configuradas por papel.

---

> **Documento gerado em**: Março de 2026
> **Base**: Análise completa do código-fonte do sistema Piuba Pescados
> **Finalidade**: Treinamento, onboarding e referência operacional
