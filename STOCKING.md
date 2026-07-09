# Stocking module (aquaculture)

This document describes the **Stocking** module: structure, routes, payloads, response format, and the **DTO + Mapper** pattern used in the project.

In aquaculture terminology, **stocking** is the act of introducing organisms (e.g. shrimp post-larvae) into a batch/tank. The module stores stocking events per batch.

## Overview

A **Stocking** record is associated with a **Batch** and stores:

- `batch_id`: reference to the batch (`batches.id`)
- `stocking_date`: date of the stocking event
- `quantity`: number of organisms stocked
- `average_weight`: average weight at stocking

## Module structure (layers)

- **Domain**
  - `app/Domain/Models/Stocking.php`
  - `app/Domain/Repositories/StockingRepositoryInterface.php`
- **Infrastructure**
  - `app/Infrastructure/Persistence/StockingRepository.php`
  - `app/Infrastructure/Mappers/StockingMapper.php`
- **Application**
  - `app/Application/DTOs/StockingDTO.php`
  - `app/Application/UseCases/Stocking/*`
- **Presentation**
  - `app/Presentation/Controllers/StockingController.php`
  - `app/Presentation/Requests/Stocking/StockingStoreRequest.php`
  - `app/Presentation/Requests/Stocking/StockingUpdateRequest.php`
  - `app/Presentation/Resources/Stocking/StockingResource.php`
- **Routes**
  - `routes/app/company/stocking.php`

## Database

- **Creation migration:** `database/migrations/2025_03_26_153914_create_settlements_table.php` (creates initial table)
- **Rename migration:** `database/migrations/2026_02_27_234826_rename_settlements_to_stockings_table.php` (renames to Stocking terminology)

**Table:** `stockings`

- `id` (uuid, PK)
- `batch_id` (uuid, FK → `batches.id`, onDelete cascade)
- `stocking_date` (date)
- `quantity` (integer)
- `average_weight` (float)
- `created_at`, `updated_at`
- `deleted_at` (soft deletes)

## Authentication and permissions

The API uses JWT (see `README.md`). For the Stocking module, permissions follow the pattern `{action}-stocking`:

- `create-stocking`
- `view-stocking`
- `update-stocking`
- `delete-stocking`

These permissions are assigned to the `company-admin` role in `database/seeders/CompanyAdminPermissionsSeeder.php`.

---

## Endpoints

Routes are defined in `routes/app/company/stocking.php`.

### Create stocking

`POST /company/stocking`

Body (camelCase):

```json
{
  "batchId": "550e8400-e29b-41d4-a716-446655440000",
  "stockingDate": "2026-02-13",
  "quantity": 100,
  "averageWeight": 1.25
}
```

Alternative input (also accepted): `batch_id`, `stocking_date`, `average_weight`.

Response (HTTP 201): `{ status, response: { id, batchId, stockingDate, quantity, averageWeight, createdAt, updatedAt }, message }`

### List stockings (paginated)

`GET /company/stockings`

Response (HTTP 200): `{ status, response: { data: [...] }, message, pagination }`

### Get stocking by ID

`GET /company/stocking/{id}`

### Update stocking

`PUT /company/stocking/{id}`

Body (partial, camelCase): `stockingDate`, `quantity`, `averageWeight`, `batchId`. Snake_case and `batch_id` are also accepted.

### Delete stocking

`DELETE /company/stocking/{id}`

Response (HTTP 200): `{ status, response: null, message: "Stocking successfully deleted" }`

---

## DTO + Mapper pattern

### Output (DTO / Resource)

The module exposes data in **camelCase** via `StockingDTO::toArray()` and `StockingResource`:

- `batchId`, `stockingDate` (YYYY-MM-DD), `averageWeight`, `createdAt`, `updatedAt`

### Input (normalisation)

- `StockingMapper::fromRequest(array $data)` maps request payload (camelCase or snake_case) to DB snake_case.
- `StockingMapper::toDTO(Stocking $model)` maps the model to `StockingDTO`.
- Store/Update requests use `prepareForValidation()` to accept `stocking_date` and other snake_case fields.

### Field mapping (API → DB)

| API (camelCase) | DB (snake_case) |
|-----------------|-----------------|
| batchId         | batch_id        |
| stockingDate    | stocking_date   |
| quantity        | quantity        |
| averageWeight   | average_weight  |

### Flow (HTTP → DB → HTTP)

- `StockingController` validates with `StockingStoreRequest` / `StockingUpdateRequest`, calls `StockingService`.
- Use cases use `StockingMapper::fromRequest()` before persisting and `StockingMapper::toDTO()` for responses.
- `ApiResponse` standardises HTTP output as `{ status, response, message, pagination? }`.
