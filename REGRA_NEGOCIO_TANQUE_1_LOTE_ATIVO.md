# Regra de Negócio: 1 lote ativo por tanque

## Contexto (como está hoje)

- A tabela `batches` possui `tank_id` **obrigatório** e `status` com valores `active|finished`.
- O tanque “atual” de um lote é representado diretamente por `batches.tank_id`.
- No fluxo de **transferência**, ao criar um `Transfer`, o sistema atualiza o `tank_id` do lote para o `destination_tank_id` (ou seja, o lote “muda de tanque”).

Isso implica que lotes “finalizados” (`finished`) continuam vinculados a um tanque, portanto **não faz sentido** restringir “1 lote total por tanque”, e sim **1 lote *ativo* por tanque**.

## Regra proposta

> **Um tanque pode ter no máximo 1 lote ativo por vez.**

Formalmente:

- Para qualquer `tank_id`, deve existir **no máximo um** registro em `batches` onde:
  - `status = 'active'`
  - `deleted_at IS NULL`

## Onde a regra deve ser aplicada

### 1) Criação de lote (Batche)

- Ao criar um lote (que por padrão nasce como `active`), **bloquear** se o tanque já possuir outro lote ativo.

### 2) Atualização de lote (Batche)

- Ao alterar `tank_id` e/ou alterar `status` para `active`, **bloquear** se o tanque de destino já possuir outro lote ativo.

### 3) Criação de transferência (Transfer)

- Antes de mover o lote para `destinationTankId`, **bloquear** se o tanque de destino já possuir outro lote ativo (diferente do próprio lote transferido).

### 4) Atualização de transferência (Transfer)

- Antes de aplicar a mudança de tanque no lote (para o `destination_tank_id` atualizado), **bloquear** se o tanque de destino já possuir outro lote ativo (diferente do lote associado ao transfer).

## Mensagens de erro sugeridas

- `Tanque já possui um lote ativo.`
- `O lote não está no tanque de origem informado.`
- `The origin tank cannot be the same as the destination tank.` (já existe no padrão atual de transfers)

## Considerações técnicas

- **Garantia forte** (à prova de concorrência) idealmente deveria existir no nível do banco.
  - Em bancos que suportam *partial unique index* (ex.: PostgreSQL), dá para garantir com um índice único parcial para `status='active' AND deleted_at IS NULL`.
  - Em MySQL, isso normalmente exige modelagem alternativa (ex.: coluna derivada, constraint via trigger, ou tabela “current_batche_per_tank”).
- **Hoje** a regra é aplicada no nível de aplicação (use cases), usando repositório, mantendo arquitetura limpa e facilitando testes/manutenção.

