# Mapa de Arquitetura — Projeto Piuba API

Estrutura completa do diretório `app/` no formato **Presentation → Application → Domain → Infrastructure**.

---

## Visão por camadas

```
app/
├── Presentation/      # HTTP, validação, formatação de resposta
├── Application/      # Services, UseCases, DTOs
├── Domain/            # Interfaces, Models, Enums, Value Objects
└── Infrastructure/    # Repositórios, Mappers, Providers
```

---

## 1. Presentation

### 1.1 Controllers
| Arquivo | Descrição |
|---------|-----------|
| `Controller.php` | Base abstrata |
| `AlertController.php` | CRUD alertas |
| `AuthController.php` | Login, permissões |
| `BatchController.php` | CRUD lotes |
| `BiometryController.php` | CRUD biometrias |
| `ClientController.php` | CRUD clientes |
| `CompanyController.php` | CRUD empresas |
| `CostAllocationController.php` | CRUD rateio de custos |
| `FeedControlController.php` | CRUD controle de ração |
| `FeedingController.php` | CRUD arraçoamentos |
| `FinancialCategoryController.php` | CRUD categorias financeiras |
| `FinancialTransactionController.php` | CRUD transações financeiras |
| `GrowthCurveController.php` | CRUD curvas de crescimento |
| `HarvestController.php` | CRUD despescas |
| `MortalityController.php` | CRUD mortalidades |
| `PurchaseController.php` | CRUD compras |
| `SaleController.php` | CRUD vendas |
| `SensorController.php` | CRUD sensores |
| `StockController.php` | CRUD estoques |
| `StockingController.php` | CRUD estocagens |
| `SubscriptionController.php` | CRUD assinaturas |
| `SupplierController.php` | CRUD fornecedores |
| `TankController.php` | CRUD tanques |
| `TransferController.php` | CRUD transferências |
| `WaterQualityController.php` | CRUD qualidade da água |

### 1.2 Requests (validação Store/Update por módulo)
```
Requests/
├── Alert/           AlertStoreRequest, AlertUpdateRequest
├── Batch/           BatchStoreRequest, BatchUpdateRequest
├── Biometry/        BiometryStoreRequest, BiometryUpdateRequest
├── Client/          ClientStoreRequest, ClientUpdateRequest
├── Company/         CompanyStoreRequest, CompanyUpdateRequest
├── CostAllocation/  CostAllocationStoreRequest, CostAllocationUpdateRequest
├── FeedControl/     FeedControlStoreRequest, FeedControlUpdateRequest
├── Feeding/         FeedingStoreRequest, FeedingUpdateRequest
├── FinancialCategory/    FinancialCategoryStoreRequest, FinancialCategoryUpdateRequest
├── FinancialTransaction/  FinancialTransactionStoreRequest, FinancialTransactionUpdateRequest
├── GrowthCurve/      GrowthCurveStoreRequest, GrowthCurveUpdateRequest
├── Harvest/         HarvestStoreRequest, HarvestUpdateRequest
├── Mortality/       MortalityStoreRequest, MortalityUpdateRequest
├── Purchase/        PurchaseStoreRequest, PurchaseUpdateRequest
├── Sale/            SaleStoreRequest, SaleUpdateRequest
├── Sensor/          SensorStoreRequest, SensorUpdateRequest
├── Stock/           StockStoreRequest, StockUpdateRequest
├── Stocking/        StockingStoreRequest, StockingUpdateRequest
├── Subscription/    SubscriptionStoreRequest, SubscriptionUpdateRequest
├── Supplier/        SupplierStoreRequest, SupplierUpdateRequest
├── Tank/            TankStoreRequest, TankUpdateRequest
├── Transfer/        TransferStoreRequest, TransferUpdateRequest
└── WaterQuality/    WaterQualityStoreRequest, WaterQualityUpdateRequest
```

### 1.3 Resources (formato JSON listagem/detalhe)
```
Resources/
├── Alert/AlertResource.php
├── Batch/BatchResource.php
├── Biometry/BiometryResource.php
├── Client/ClientResource.php
├── Company/CompanyResource.php
├── CostAllocation/CostAllocationResource.php
├── FeedControl/FeedControlResource.php
├── Feeding/FeedingResource.php
├── FinancialCategory/FinancialCategoryResource.php
├── FinancialTransaction/FinancialTransactionResource.php
├── GrowthCurve/GrowthCurveResource.php
├── Harvest/HarvestResource.php
├── Mortality/MortalityResource.php
├── Purchase/PurchaseResource.php
├── Sale/SaleResource.php
├── Sensor/SensorResource.php
├── Stock/StockResource.php
├── Stocking/StockingResource.php
├── Subscription/SubscriptionResource.php
├── Supplier/SupplierResource.php
├── Tank/TankResource.php
├── Transfer/TransferResource.php
├── WaterQuality/WaterQualityResource.php
└── (Response) ApiResponse.php → Presentation/Response/ApiResponse.php
```

### 1.4 Middleware
```
Middleware/
├── ApiAuthenticate.php
├── CheckPermission.php
├── CheckRole.php
├── CorsMiddleware.php
├── ForceJsonResponse.php
├── RateLimitMiddleware.php
├── SanitizeInputMiddleware.php
├── SecurityHeadersMiddleware.php
└── TrimStrings.php
```

### 1.5 Outros (Presentation)
```
Exceptions/Handler.php
Response/ApiResponse.php
```

---

## 2. Application

### 2.1 Services (um por domínio; orquestram UseCases)
```
Services/
├── AlertService.php
├── AuthService.php
├── BatchService.php
├── BiometryFcrService.php      # FCR / biometria
├── BiometryService.php
├── ClientService.php
├── CompanyService.php
├── CostAllocationService.php
├── FeedControlService.php
├── FeedingService.php
├── FinancialCategoryService.php
├── FinancialTransactionService.php
├── GrowthCurveService.php
├── HarvestService.php
├── MortalityService.php
├── PurchaseService.php
├── SaleService.php
├── SensorService.php
├── StockingService.php
├── StockService.php
├── SubscriptionService.php
├── SupplierService.php
├── TankService.php
├── TransferService.php
└── WaterQualityService.php
```

### 2.2 DTOs
```
DTOs/
├── AlertDTO.php
├── BatchDTO.php
├── BiometryDTO.php
├── ClientDTO.php
├── CompanyDTO.php
├── CostAllocationDTO.php
├── FeedControlDTO.php
├── FeedingDTO.php
├── FinancialCategoryDTO.php
├── FinancialTransactionDTO.php
├── GrowthCurveDTO.php
├── HarvestDTO.php
├── LoginCredentialsDTO.php
├── MortalityDTO.php
├── PurchaseDTO.php
├── SaleDTO.php
├── SensorDTO.php
├── StockDTO.php
├── StockingDTO.php
├── SubscriptionDTO.php
├── SupplierDTO.php
├── TankDTO.php
├── TransferDTO.php
└── WaterQualityDTO.php
```

### 2.3 UseCases (por módulo)
```
UseCases/
├── Alert/       CreateAlertUseCase, DeleteAlertUseCase, ListAlertsUseCase, ShowAlertUseCase, UpdateAlertUseCase
├── Auth/        AuthenticateUserUseCase, CheckUserPermissionUseCase, ResolveUserPermissionsUseCase
├── Batch/       CreateBatchUseCase, DeleteBatchUseCase, ListBatchesUseCase, ShowBatchUseCase, UpdateBatchUseCase
├── Biometry/    CreateBiometryUseCase, DeleteBiometryUseCase, ListBiometriesUseCase, ShowBiometryUseCase, UpdateBiometryUseCase
├── Client/      CreateClientUseCase, DeleteClientUseCase, ListClientsUseCase, ShowClientUseCase, UpdateClientUseCase
├── Company/     CreateCompanyUseCase, DeleteCompanyUseCase, ShowAllCompaniesUseCase, ShowCompanyUseCase, UpdateCompanyUseCase
├── CostAllocation/  Create, Delete, List, Show, Update
├── FeedControl/     Create, Delete, List, Show, Update
├── Feeding/         CreateFeedingUseCase, DeleteFeedingUseCase, ListFeedingsUseCase, ShowFeedingUseCase, UpdateFeedingUseCase
├── FinancialCategory/    Create, Delete, List, Show, Update
├── FinancialTransaction/ Create, Delete, List, Show, Update
├── GrowthCurve/  Create, Delete, List, Show, Update
├── Harvest/      Create, Delete, List, Show, Update
├── Mortality/    Create, Delete, List, Show, Update
├── Purchase/     Create, Delete, List, Show, Update
├── Sale/         Create, Delete, List, Show, Update
├── Sensor/       Create, Delete, List, Show, Update
├── Stock/        CreateStockUseCase, DeleteStockUseCase, ListStocksUseCase, ShowStockUseCase, UpdateStockUseCase
├── Stocking/     Create, Delete, List, Show, Update
├── Subscription/ Create, Delete, List, Show, Update
├── Supplier/     Create, Delete, List, Show, Update
├── Tank/         CreateTankUseCase, DeleteTankUseCase, GetTankTypesUseCase, ShowAllTanksUseCase, ShowTanksWithoutBatchesUseCase, ShowTankUseCase, UpdateTankUseCase
├── Transfer/     Create, Delete, List, Show, Update
└── WaterQuality/ Create, Delete, List, Show, Update
```

---

## 3. Domain

### 3.1 Repositories (interfaces)
```
Repositories/
├── PaginationInterface.php
├── AlertRepositoryInterface.php
├── AuthRepositoryInterface.php
├── BatchRepositoryInterface.php
├── BiometryRepositoryInterface.php
├── ClientRepositoryInterface.php
├── CompanyRepositoryInterface.php
├── CostAllocationRepositoryInterface.php
├── FeedControlRepositoryInterface.php
├── FeedingRepositoryInterface.php
├── FinancialCategoryRepositoryInterface.php
├── FinancialTransactionRepositoryInterface.php
├── GrowthCurveRepositoryInterface.php
├── HarvestRepositoryInterface.php
├── MortalityRepositoryInterface.php
├── PurchaseRepositoryInterface.php
├── SaleRepositoryInterface.php
├── SensorRepositoryInterface.php
├── StockRepositoryInterface.php
├── StockingRepositoryInterface.php
├── SubscriptionRepositoryInterface.php
├── SupplierRepositoryInterface.php
├── TankRepositoryInterface.php
├── TransferRepositoryInterface.php
└── WaterQualityRepositoryInterface.php
```

### 3.2 Models (Eloquent)
```
Models/
├── BaseModel.php
├── Alert.php
├── Batch.php
├── Biometry.php
├── Client.php
├── Company.php
├── CostAllocation.php
├── FeedControl.php
├── Feeding.php
├── FinancialCategory.php
├── FinancialTransaction.php
├── GrowthCurve.php
├── Harvest.php
├── Mortality.php
├── Permission.php
├── Purchase.php
├── Role.php
├── Sale.php
├── Sensor.php
├── Stock.php
├── Stocking.php
├── Subscription.php
├── Supplier.php
├── Tank.php
├── TankType.php
├── Transfer.php
├── User.php
└── WaterQuality.php
```

### 3.3 Enums
```
Enums/
├── Can.php
├── Cultivation.php
├── FinancialType.php
├── SensorType.php
└── Status.php
```

### 3.4 Value Objects
```
ValueObjects/
├── Address.php
├── BatchId.php
├── CapacityLiters.php
├── CNPJ.php
├── CompanyId.php
├── Email.php
├── EntryDate.php
├── InitialQuantity.php
├── Location.php
├── Name.php
├── Permission.php
├── Phone.php
├── Species.php
├── TankId.php
└── UserId.php
```

---

## 4. Infrastructure

### 4.1 Persistence (implementações dos repositórios)
```
Persistence/
├── PaginationPresentr.php       # Implementa PaginationInterface
├── AlertRepository.php
├── AuthRepository.php
├── BatchRepository.php
├── BiometryRepository.php
├── ClientRepository.php
├── CompanyRepository.php
├── CostAllocationRepository.php
├── FeedControlRepository.php
├── FeedingRepository.php
├── FinancialCategoryRepository.php
├── FinancialTransactionRepository.php
├── GrowthCurveRepository.php
├── HarvestRepository.php
├── MortalityRepository.php
├── PurchaseRepository.php
├── SaleRepository.php
├── SensorRepository.php
├── StockRepository.php
├── StockingRepository.php
├── SubscriptionRepository.php
├── SupplierRepository.php
├── TankRepository.php
├── TransferRepository.php
└── WaterQualityRepository.php
```

### 4.2 Mappers (request/response ↔ persistência)
```
Mappers/
├── BatchMapper.php
├── BiometryMapper.php
├── CompanyMapper.php
├── FeedingMapper.php
├── StockingMapper.php
├── TankMapper.php
├── TransferMapper.php
└── UserMapper.php
```

### 4.3 Providers
```
Providers/
├── AppServiceProvider.php
├── AuthServiceProvider.php
└── RouteServiceProvider.php
```

---

## 5. Árvore resumida (estilo do mapa Feeding)

```
app/
├── Presentation/
│   ├── Controllers/           # 25 controllers (Alert, Auth, Batch, …)
│   ├── Requests/              # Store + Update por módulo (Alert, Batch, Feeding, …)
│   ├── Resources/             # 24 resources (formato JSON)
│   ├── Middleware/             # Auth, permissão, CORS, rate limit, etc.
│   ├── Exceptions/Handler.php
│   └── Response/ApiResponse.php
│
├── Application/
│   ├── Services/              # 27 services (um por domínio)
│   ├── DTOs/                  # 26 DTOs
│   └── UseCases/              # 24 módulos (Alert, Auth, Batch, …), cada um com Create, List, Show, Update, Delete (+ específicos)
│
├── Domain/
│   ├── Repositories/          # 25 interfaces + PaginationInterface
│   ├── Models/                # 27 models (BaseModel + entidades)
│   ├── Enums/                 # 5 enums
│   └── ValueObjects/          # 15 value objects
│
└── Infrastructure/
    ├── Persistence/           # 25 repositórios + PaginationPresentr
    ├── Mappers/               # 8 mappers (Batch, Biometry, Company, Feeding, Stocking, Tank, Transfer, User)
    └── Providers/             # App, Auth, Route
```

---

## 6. Fluxo genérico (CRUD)

Para qualquer recurso (ex.: Feeding, Batch, Biometry):

1. **HTTP** → **Controller** (usa Request validado)
2. **Controller** → **Service** (método create/update/list/show/delete)
3. **Service** → **UseCase** (execute)
4. **UseCase** → **RepositoryInterface** (injetada; implementação em Infrastructure)
5. **Repository** → **Model** (Eloquent)
6. **UseCase** → **Mapper** (quando existe) para request → array e model → DTO
7. Resposta: **DTO** ou **Resource** → **ApiResponse** → JSON

Módulos **com Mapper** (transformação explícita): Batch, Biometry, Company, Feeding, Stocking, Tank, Transfer (e User para auth).

Os demais podem usar DTOs construídos direto no UseCase ou via Resource na listagem.

---

## 7. Legenda

| Sigla / termo | Significado |
|---------------|-------------|
| Store | Validação do POST (criar) |
| Update | Validação do PUT/PATCH |
| Resource | Formato da resposta JSON (listagem/detalhe) |
| DTO | Data Transfer Object (camelCase, isolamento da API) |
| Mapper | Converte request → array persistência; model → DTO |

Este mapa cobre o projeto todo no mesmo padrão do mapa do módulo Feeding.
