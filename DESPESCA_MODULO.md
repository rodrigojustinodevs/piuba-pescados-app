# Módulo de Despesca (Harvest)

## Visão Geral

A **despesca** representa o processo de coleta/captura de peixes de um lote de produção. É o evento que encerra (total ou parcialmente) o ciclo produtivo de um lote, registrando a quantidade coletada, classificação por tamanho, destino do produto e indicadores financeiros.

Cada despesca está obrigatoriamente vinculada a um **lote (batch)** e opcionalmente a um **tanque (tank)**.

---

## Entidade Harvest

| Campo | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | UUID | — | Identificador único (gerado automaticamente) |
| `batch_id` | UUID | Sim | Lote ao qual pertence a despesca |
| `tank_id` | UUID | Não | Tanque específico (pode ser nulo) |
| `harvest_date` | Date | Sim | Data em que a despesca foi realizada |
| `type` | Enum `HarvestType` | Sim | Tipo de despesca |
| `status` | Enum `HarvestStatus` | Sim | Status atual (padrão: `completed`) |
| `destination` | Enum `HarvestDestination` | Não | Destino do produto colhido |
| `initial_population` | Integer | Sim | População inicial do lote/tanque antes da despesca |
| `harvested_quantity` | Integer | Sim | Quantidade de peixes efetivamente colhida |
| `average_weight` | Float (g) | Sim | Peso médio individual dos peixes (em gramas) |
| `total_weight` | Float (kg) | Calculado | Peso total colhido (calculado das classificações) |
| `price_per_kg` | Float | Calculado | Preço médio ponderado por kg |
| `total_revenue` | Float | Calculado | Receita total da despesca |
| `operational_cost` | Float | Não | Custos operacionais da despesca |
| `client_destination` | String | Não | Cliente ou destino específico |
| `responsible` | String | Não | Responsável pela execução da despesca |
| `notes` | Text | Não | Observações livres |
| `deleted_at` | Timestamp | — | Soft delete — registros não são excluídos fisicamente |

---

## Enums

### HarvestType — Tipo de Despesca

| Valor | Descrição |
|---|---|
| `total` | Despesca completa — todo o lote é colhido |
| `partial` | Despesca parcial — apenas uma parte do lote é colhida |
| `selective` | Despesca seletiva — seleção específica por tamanho ou critério |
| `emergency` | Despesca emergencial — motivada por condições adversas (doença, clima, etc.) |

### HarvestStatus — Status da Despesca

| Valor | Descrição |
|---|---|
| `completed` | Despesca concluída (padrão ao criar) |
| `scheduled` | Despesca agendada para data futura |
| `in_progress` | Despesca em andamento |
| `cancelled` | Despesca cancelada |

### HarvestDestination — Destino do Produto

| Valor | Descrição |
|---|---|
| `wholesale` | Atacado |
| `retail` | Varejo |
| `processing` | Beneficiamento/Processamento |
| `restaurant` | Fornecimento para restaurantes |
| `live_market` | Mercado de peixe vivo |
| `internal` | Consumo interno |

---

## Classificações por Tamanho (HarvestSizeClassification)

Cada despesca pode ter uma ou mais classificações por tamanho. Elas detalham a distribuição dos peixes colhidos por categoria/classe.

| Campo | Tipo | Descrição |
|---|---|---|
| `harvest_id` | UUID | Referência à despesca pai |
| `class` | String (máx. 10) | Identificação da classe (ex: "G", "M", "P", "GG") |
| `quantity` | Integer | Quantidade de peixes nesta classe |
| `average_weight` | Float (g) | Peso médio dos peixes desta classe (em gramas) |
| `price_per_kg` | Float | Preço por kg para esta classe |

**Ao menos uma classificação é obrigatória** no cadastro da despesca.

---

## Cálculos Automáticos

Todos os campos calculados são derivados das classificações por tamanho e persistidos na entidade Harvest.

### Peso Total (`total_weight`)
```
total_weight = Σ (quantity × average_weight / 1000)  [em kg]
```
Arredondado para 3 casas decimais.

### Receita Total (`total_revenue`)
```
total_revenue = Σ (total_weight_da_classe × price_per_kg_da_classe)
```
Arredondado para 2 casas decimais.

### Preço Médio por kg (`price_per_kg`)
```
price_per_kg = total_revenue / total_weight   (se total_weight > 0, caso contrário = 0)
```
Arredondado para 2 casas decimais. Representa o preço médio ponderado considerando todas as classes.

### Taxa de Sobrevivência (`survivalRate`) — campo calculado na leitura
```
survival_rate = (harvested_quantity / initial_population) × 100   [%]
```

### Lucro Líquido (`netProfit`) — campo calculado na leitura
```
net_profit = total_revenue - operational_cost
```

---

## Regras de Criação

### Validações obrigatórias
- `batch_id`: UUID válido e existente na tabela `batches`
- `tank_id`: UUID válido e existente na tabela `tanks` (quando informado)
- `type`: valor válido do enum `HarvestType`
- `status`: valor válido do enum `HarvestStatus`
- `destination`: valor válido do enum `HarvestDestination` (quando informado)
- `initial_population`, `harvested_quantity`, `average_weight`, `operational_cost`: numérico ≥ 0
- `size_classifications`: array com **no mínimo 1 item**, cada item com `class`, `quantity`, `average_weight` e `price_per_kg`

### Lógica de persistência
1. Todos os campos agregados (`total_weight`, `total_revenue`, `price_per_kg`) são calculados a partir das classificações antes de salvar.
2. A despesca e suas classificações são criadas dentro de uma **transação de banco de dados** — se qualquer etapa falhar, tudo é revertido.

---

## Regras de Atualização

- Todos os campos são **opcionais** na atualização (usa `sometimes`).
- Se o array `size_classifications` for enviado, as classificações existentes são **todas deletadas** e substituídas pelas novas (padrão *replace-all*, não merge).
- Os campos calculados (`total_weight`, `total_revenue`, `price_per_kg`) são recalculados automaticamente se novas classificações forem enviadas.
- A operação é transacional — falhas revertem todas as alterações.

---

## Endpoints da API

Todos os endpoints estão sob o prefixo `/company` e requerem autenticação JWT.

| Método | Rota | Permissão | Ação |
|---|---|---|---|
| GET | `/company/harvests` | `view-harvest` | Lista todas as despescas (paginado, 25 por página) |
| GET | `/company/harvest/{id}` | `view-harvest` | Retorna uma despesca com todas as relações |
| POST | `/company/harvest` | `create-harvest` | Cria nova despesca com classificações |
| PUT | `/company/harvest/{id}` | `update-harvest` | Atualiza despesca (parcial ou total) |
| DELETE | `/company/harvest/{id}` | `delete-harvest` | Soft delete da despesca |

### Relações carregadas na leitura
- **Lote** (`batch`): id e nome
- **Tanque** (`tank`): id e nome
- **Classificações** (`size_classifications`): todos os registros com campos calculados

---

## Integrações

### Lote (Batch)
- Toda despesca pertence a um lote (`batch_id` obrigatório).
- Se o lote for deletado, a despesca também é deletada em cascata.

### Tanque (Tank)
- A referência ao tanque é opcional.
- Se o tanque for deletado, o campo `tank_id` é definido como `null` (set null on delete).

### Sistema de Permissões
As permissões `create-harvest`, `view-harvest`, `update-harvest` e `delete-harvest` devem estar associadas ao papel (role) do usuário para acessar os endpoints.

---

## Soft Delete

A entidade usa `SoftDeletes` do Laravel. Registros deletados recebem o timestamp em `deleted_at` e são excluídos das consultas padrão, mas podem ser restaurados.
