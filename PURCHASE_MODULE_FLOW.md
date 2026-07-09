# Purchase Module Flow

## Camadas e arquivos (Create/Update)

### Request
- `app/Presentation/Requests/Purchase/PurchaseStoreRequest.php`
- `app/Presentation/Requests/Purchase/PurchaseUpdateRequest.php`

### Controller
- `app/Presentation/Controllers/PurchaseController.php`
  - `store()`
  - `update()`

### UseCases
- `app/Application/UseCases/Purchase/CreatePurchaseUseCase.php`
- `app/Application/UseCases/Purchase/UpdatePurchaseUseCase.php`

### Services
- `app/Domain/Services/Purchase/PurchaseService.php`
  - `createPurchase()`
  - `update()`
  - `syncItems()`

### Repositories
- `app/Domain/Repositories/PurchaseRepositoryInterface.php`
- `app/Infrastructure/Persistence/PurchaseRepository.php`

### Models
- `app/Domain/Models/Purchase.php`
- `app/Domain/Models/PurchaseItem.php`

### Entities (Domínio)
- `app/Domain/Entities/Purchase.php`
- `app/Domain/Entities/PurchaseItem.php`

### Mappers
- `app/Infrastructure/Mappers/PurchaseMapper.php`
- `app/Infrastructure/Mappers/PurchaseItemMapper.php`

### DTOs
- `app/Application/DTOs/PurchaseDTO.php`
- `app/Application/DTOs/PurchaseItemDTO.php`

---

## Fluxo Create

1. `PurchaseController@store` recebe `PurchaseStoreRequest`.
2. `CreatePurchaseUseCase` inicia transação.
3. `PurchaseMapper::toEntity($data)` converte payload em `Purchase` (Entity) + `PurchaseItem` (Entity).
4. `PurchaseService::createPurchase($entity)` calcula total no backend e chama repository.
5. `PurchaseRepository::create($payload)` persiste purchase e purchase_items.
6. `PurchaseMapper::toDTO($model)` retorna `PurchaseDTO`.

Regra principal:
- `totalPrice` e `item.totalPrice` são calculados no backend (Entity/Service), não confiados do frontend.

---

## Fluxo Update

1. `PurchaseController@update` recebe `PurchaseUpdateRequest`.
2. `UpdatePurchaseUseCase` busca purchase existente e inicia transação.
3. Mapper converte payload para Entity e depois para primitives.
4. `PurchaseService::update($purchase, $data)`:
   - atualiza cabeçalho via repository;
   - sincroniza itens em `purchase_items` (`syncItems`);
   - recalcula `total_price` por item no backend.
5. Mapper converte model atualizado para `PurchaseDTO`.

Regra crítica:
- `items` não é atualizado na tabela `purchases` (coluna inexistente). Itens ficam só em `purchase_items`.

---

## Regras de domínio aplicadas

- `PurchaseItem` calcula total via `unitPrice * quantity`.
- `Purchase` mantém total como soma dos itens.
- Valores financeiros são definidos no backend.
- `strict_types=1` em todos os arquivos do módulo.

---

## Payload de exemplo (Create)

```json
{
  "supplierId": "79a92738-66e7-49c2-bae5-d27c87a689ce",
  "purchaseDate": "2026-03-20",
  "items": [
    {
      "supplyId": "8de14b62-622c-46b2-9f54-874bba7ea803",
      "quantity": 5,
      "unit": "kg",
      "unitPrice": 11
    }
  ]
}
```

---

## Checklist técnico

- [x] Requests validados
- [x] Controller delegando para use cases
- [x] Use cases com transação
- [x] Entity + Mapper + DTO
- [x] Service com regra de negócio
- [x] Repository persistindo cabeçalho e itens separadamente

