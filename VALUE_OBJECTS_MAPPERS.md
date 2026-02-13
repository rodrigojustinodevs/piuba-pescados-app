# Value Objects e Mappers - Documentação

## 📋 Visão Geral

Esta documentação descreve a implementação de **Value Objects** e **Mappers** no projeto, seguindo os princípios de DDD (Domain-Driven Design) e Clean Architecture.

## 🎯 Objetivos

- **Imutabilidade**: Value Objects são imutáveis após criação
- **Validação de Domínio**: Regras de negócio validadas no construtor
- **Isolamento**: Domain não conhece Infrastructure
- **Tipagem Forte**: PHP 8.3 com tipos explícitos
- **Reutilização**: Mappers centralizam transformações

---

## 📦 Value Objects

### Localização
`app/Domain/ValueObjects/`

### Características Obrigatórias

✅ Classe `final`  
✅ Propriedades `private readonly`  
✅ Validação no construtor  
✅ Sem dependências de Eloquent, Request ou Infrastructure  
✅ Sem setters (imutável)  
✅ Método `equals()` para comparação por valor  
✅ Método `value()` ou `toString()`  

### Value Objects Implementados

#### 1. Email
```php
use App\Domain\ValueObjects\Email;

$email = new Email('user@example.com');
echo $email->value(); // 'user@example.com'
echo $email->toString(); // 'user@example.com'

// Validação automática
try {
    $invalid = new Email('invalid-email');
} catch (InvalidArgumentException $e) {
    // Email inválido
}
```

#### 2. Name
```php
use App\Domain\ValueObjects\Name;

$name = new Name('João Silva');
echo $name->value(); // 'João Silva' (trimmed)

// Validações:
// - Não pode ser vazio
// - Mínimo 2 caracteres
// - Máximo 255 caracteres
```

#### 3. CNPJ
```php
use App\Domain\ValueObjects\CNPJ;

$cnpj = new CNPJ('12.345.678/0001-90');
echo $cnpj->value(); // '12345678000190' (apenas números)
echo $cnpj->formatted(); // '12.345.678/0001-90'

// Validação automática de dígitos verificadores
```

#### 4. Phone
```php
use App\Domain\ValueObjects\Phone;

$phone = new Phone('(11) 98765-4321');
echo $phone->value(); // '11987654321'
echo $phone->formatted(); // '(11) 98765-4321'

// Aceita 10 ou 11 dígitos
```

#### 5. Address
```php
use App\Domain\ValueObjects\Address;

$address = Address::fromArray([
    'street' => 'Rua das Flores',
    'number' => '123',
    'city' => 'São Paulo',
    'state' => 'SP',
    'zipCode' => '01234-567'
]);

$array = $address->toArray();
```

#### 6. UserId / CompanyId
```php
use App\Domain\ValueObjects\UserId;
use App\Domain\ValueObjects\CompanyId;

// Criar a partir de string UUID
$userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');

// Gerar novo UUID
$newUserId = UserId::generate();

echo $userId->value(); // UUID string
```

#### 7. Permission
```php
use App\Domain\ValueObjects\Permission;

$permission = Permission::fromString('user-create');
echo $permission->value(); // 'user-create'

// Validação: formato 'palavra-palavra'
```

#### 8. Location
```php
use App\Domain\ValueObjects\Location;

$location = new Location('Setor A - Bloco 3');
echo $location->value(); // 'Setor A - Bloco 3' (trimmed)

// Validações:
// - Não pode ser vazio
// - Mínimo 3 caracteres
// - Máximo 255 caracteres
```

#### 9. CapacityLiters
```php
use App\Domain\ValueObjects\CapacityLiters;

$capacity = CapacityLiters::fromInt(1000);
echo $capacity->value(); // 1000

// Validações:
// - Mínimo 1 litro
// - Máximo 1.000.000.000 litros
```

#### 10. TankId
```php
use App\Domain\ValueObjects\TankId;

// Criar a partir de string UUID
$tankId = TankId::fromString('550e8400-e29b-41d4-a716-446655440000');

// Gerar novo UUID
$newTankId = TankId::generate();

echo $tankId->value(); // UUID string
```

---

## 🔄 Mappers

### Localização
`app/Infrastructure/Mappers/`

### Responsabilidades

✅ Converter `Model` ↔ `DTO`  
✅ Converter `array` ↔ `DTO`  
✅ Encapsular criação de Value Objects  
✅ Nunca acessar Request, Auth ou Facades  
✅ Métodos estáticos ou serviços puros  

### Mappers Implementados

#### 1. CompanyMapper

**Converter Model para DTO:**
```php
use App\Infrastructure\Mappers\CompanyMapper;
use App\Domain\Models\Company;

$company = Company::find($id);
$dto = CompanyMapper::toDTO($company);
```

**Converter Request para Array de Persistência:**
```php
use App\Infrastructure\Mappers\CompanyMapper;

$requestData = [
    'name' => 'Empresa XYZ',
    'cnpj' => '12.345.678/0001-90',
    'email' => 'contato@empresa.com',
    'phone' => '(11) 98765-4321',
    'address' => [
        'street' => 'Rua das Flores',
        'number' => '123',
        'city' => 'São Paulo',
        'state' => 'SP',
        'zipCode' => '01234-567'
    ],
    'active' => true
];

// Mapper valida e converte usando Value Objects
$mappedData = CompanyMapper::fromRequest($requestData);
// Retorna array pronto para persistência com validações aplicadas
```

**Converter DTO para Array:**
```php
use App\Infrastructure\Mappers\CompanyMapper;
use App\Application\DTOs\CompanyDTO;

$dto = new CompanyDTO(...);
$array = CompanyMapper::toArray($dto);
```

#### 2. UserMapper

```php
use App\Infrastructure\Mappers\UserMapper;
use App\Domain\Models\User;

// Converter Model para Array
$user = User::find($id);
$array = UserMapper::toArray($user);

// Converter Request para Array de Persistência
$requestData = [
    'name' => 'João Silva',
    'email' => 'joao@example.com',
    'password' => 'secret123'
];

$mappedData = UserMapper::fromRequest($requestData);
```

#### 3. TankMapper

**Converter Model para DTO:**
```php
use App\Infrastructure\Mappers\TankMapper;
use App\Domain\Models\Tank;

$tank = Tank::find($id);
$dto = TankMapper::toDTO($tank);
```

**Converter Request para Array de Persistência:**
```php
use App\Infrastructure\Mappers\TankMapper;

$requestData = [
    'name' => 'Tanque 1',
    'companyId' => '550e8400-e29b-41d4-a716-446655440000',
    'tankTypeId' => '660e8400-e29b-41d4-a716-446655440001',
    'capacityLiters' => 1000,
    'location' => 'Setor A - Bloco 3',
    'status' => 'active'
];

// Mapper valida e converte usando Value Objects
$mappedData = TankMapper::fromRequest($requestData);
// Retorna array pronto para persistência com validações aplicadas
// {
//   'name' => 'Tanque 1',
//   'company_id' => '550e8400-e29b-41d4-a716-446655440000',
//   'tank_types_id' => '660e8400-e29b-41d4-a716-446655440001',
//   'capacity_liters' => 1000,
//   'location' => 'Setor A - Bloco 3',
//   'status' => 'active'
// }
```

**Converter DTO para Array:**
```php
use App\Infrastructure\Mappers\TankMapper;
use App\Application\DTOs\TankDTO;

$dto = new TankDTO(...);
$array = TankMapper::toArray($dto);
```

---

## 🔗 Integração com Use Cases

### Antes (sem Mappers)

```php
class CreateCompanyUseCase
{
    public function execute(array $data): CompanyDTO
    {
        // Lógica de transformação misturada com regras de negócio
        if (isset($data['address']) && is_array($data['address'])) {
            $address = $data['address'];
            $data['address_street'] = $address['street'] ?? null;
            // ... muitas linhas
        }
        
        if (isset($data['active'])) {
            $data['status'] = $data['active'] ? 'active' : 'inactive';
        }
        
        $company = $this->repository->create($data);
        
        // Transformação manual para DTO
        return CompanyDTO::fromArray([...]);
    }
}
```

### Depois (com Mappers)

```php
class CreateCompanyUseCase
{
    public function execute(array $data): CompanyDTO
    {
        return DB::transaction(function () use ($data): CompanyDTO {
            // Mapper encapsula transformações e validações
            $mappedData = CompanyMapper::fromRequest($data);
            
            // UseCase foca apenas em regras de negócio
            $company = $this->repository->create($mappedData);
            
            // Mapper converte Model para DTO
            return CompanyMapper::toDTO($company);
        });
    }
}
```

### Benefícios

✅ **UseCase mais limpo**: Foca apenas em regras de negócio  
✅ **Transformações testáveis**: Mappers podem ser testados isoladamente  
✅ **Reutilização**: Mappers podem ser usados em múltiplos UseCases  
✅ **Validação centralizada**: Value Objects garantem dados válidos  
✅ **Manutenibilidade**: Mudanças em transformações ficam isoladas  

---

## 📝 Exemplo Completo de Fluxo

### 1. Request chega no Controller

```php
class CompanyController
{
    public function store(CreateCompanyRequest $request)
    {
        // Request valida formato (Laravel Validation)
        $data = $request->validated();
        
        // Service/UseCase recebe dados já validados
        $dto = $this->companyService->create($data);
        
        return new CompanyResource($dto);
    }
}
```

### 2. UseCase usa Mapper

```php
class CreateCompanyUseCase
{
    public function execute(array $data): CompanyDTO
    {
        return DB::transaction(function () use ($data): CompanyDTO {
            // Mapper valida e transforma usando Value Objects
            $mappedData = CompanyMapper::fromRequest($data);
            
            // Repository persiste
            $company = $this->repository->create($mappedData);
            
            // Mapper converte para DTO
            return CompanyMapper::toDTO($company);
        });
    }
}
```

### 3. Mapper cria Value Objects

```php
final class CompanyMapper
{
    public static function fromRequest(array $data): array
    {
        // Value Objects validam automaticamente
        $name = new Name($data['name']);
        $cnpj = new CNPJ($data['cnpj']);
        $email = new Email($data['email']);
        $phone = new Phone($data['phone']);
        $address = Address::fromArray($data['address']);
        
        return [
            'name' => $name->value(),
            'cnpj' => $cnpj->value(),
            'email' => $email->value(),
            'phone' => $phone->value(),
            // ... address fields
        ];
    }
}
```

### Exemplo: Tank com Mapper

```php
final class TankMapper
{
    public static function fromRequest(array $data): array
    {
        // Value Objects validam automaticamente
        $name = new Name($data['name']);
        $capacity = CapacityLiters::fromInt((int) $data['capacityLiters']);
        $location = new Location($data['location']);
        
        return [
            'name' => $name->value(),
            'capacity_liters' => $capacity->value(),
            'location' => $location->value(),
            'company_id' => $data['companyId'], // Converte camelCase para snake_case
            'tank_types_id' => $data['tankTypeId'],
            'status' => $data['status'] ?? 'active',
        ];
    }
}
```

---

## ⚠️ Regras Importantes

### ❌ NÃO fazer:

- ❌ Criar Value Objects "anêmicos" (sem validação)
- ❌ Usar arrays crus dentro do Domain
- ❌ Duplicar validações do Domain em Requests
- ❌ Violar dependência inversa (Domain não depende de Infrastructure)
- ❌ Acessar Request, Auth ou Facades em Mappers
- ❌ Criar setters em Value Objects

### ✅ Fazer:

- ✅ Sempre validar no construtor do Value Object
- ✅ Usar Mappers para todas as transformações
- ✅ Manter Value Objects imutáveis
- ✅ Usar tipos explícitos (PHP 8.3)
- ✅ Comparar Value Objects com `equals()`
- ✅ Criar Value Objects para conceitos de domínio

---

## 🧪 Testando Value Objects

```php
use App\Domain\ValueObjects\Email;
use InvalidArgumentException;

test('email valida formato correto', function () {
    $email = new Email('user@example.com');
    expect($email->value())->toBe('user@example.com');
});

test('email lança exceção para formato inválido', function () {
    expect(fn() => new Email('invalid-email'))
        ->toThrow(InvalidArgumentException::class);
});

test('email compara corretamente', function () {
    $email1 = new Email('user@example.com');
    $email2 = new Email('user@example.com');
    $email3 = new Email('other@example.com');
    
    expect($email1->equals($email2))->toBeTrue();
    expect($email1->equals($email3))->toBeFalse();
});
```

---

## 📚 Referências

- **DDD (Domain-Driven Design)**: Value Objects representam conceitos de domínio
- **Clean Architecture**: Mappers isolam transformações entre camadas
- **SOLID**: Single Responsibility aplicado em cada Value Object e Mapper
- **PHP 8.3**: Uso de `readonly` properties e tipos explícitos

---

## 📋 Exemplos de Request por Entidade

### Company Request (camelCase)

```json
{
  "name": "Empresa XYZ",
  "cnpj": "12.345.678/0001-90",
  "email": "contato@empresa.com",
  "phone": "(11) 98765-4321",
  "addressStreet": "Rua das Flores",
  "addressNumber": "123",
  "addressComplement": "Apto 45",
  "addressNeighborhood": "Centro",
  "addressCity": "São Paulo",
  "addressState": "SP",
  "addressZipCode": "01234-567",
  "active": true
}
```

**Campos convertidos pelo Mapper:**
- `addressStreet` → `address_street`
- `addressNumber` → `address_number`
- `addressComplement` → `address_complement`
- `addressNeighborhood` → `address_neighborhood`
- `addressCity` → `address_city`
- `addressState` → `address_state`
- `addressZipCode` → `address_zip_code`
- `active` → `status` ('active' ou 'inactive')

### Tank Request (camelCase)

```json
{
  "name": "Tanque 1",
  "companyId": "550e8400-e29b-41d4-a716-446655440000",
  "tankTypeId": "660e8400-e29b-41d4-a716-446655440001",
  "capacityLiters": 1000,
  "location": "Setor A - Bloco 3",
  "status": "active"
}
```

**Campos convertidos pelo Mapper:**
- `companyId` → `company_id`
- `tankTypeId` → `tank_types_id`
- `capacityLiters` → `capacity_liters`

**Validações aplicadas pelos Value Objects:**
- `name`: Mínimo 2 caracteres, máximo 255 (Name)
- `capacityLiters`: Mínimo 1 litro, máximo 1 bilhão (CapacityLiters)
- `location`: Mínimo 3 caracteres, máximo 255 (Location)

---

## 🚀 Próximos Passos

1. ✅ Value Objects básicos criados (Email, Name, CNPJ, Phone, Address, Location, CapacityLiters)
2. ✅ Value Objects de ID criados (UserId, CompanyId, TankId)
3. ✅ Mappers implementados (CompanyMapper, UserMapper, TankMapper)
4. ✅ Requests atualizados para usar camelCase (Company, Tank)
5. ⏳ Criar Mappers para outras entidades (Client, Supplier, etc.)
6. ⏳ Refatorar UseCases existentes para usar Mappers
7. ⏳ Adicionar testes unitários para Value Objects e Mappers

