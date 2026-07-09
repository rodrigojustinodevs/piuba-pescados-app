# Guia de Teste - JWT com Roles

Este documento descreve como testar a implementação de roles no token JWT.

## 🧪 Testes Automatizados

Execute os testes automatizados usando Pest:

```bash
# Executar todos os testes
docker exec -it api_piuba_pescados_app php artisan test

# Executar apenas os testes de JWT com roles
docker exec -it api_piuba_pescados_app php artisan test --filter JwtRolesTest

# Executar com output detalhado
docker exec -it api_piuba_pescados_app php artisan test --filter JwtRolesTest --verbose
```

## 🔍 Teste Manual via API

### 1. Fazer Login e Obter Token

```bash
curl -X POST http://localhost:8005/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "master.admin@piuba.com",
    "password": "password123"
  }'
```

**Resposta esperada:**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  },
  "message": "Authenticated successfully"
}
```

### 2. Decodificar o Token JWT

Você pode usar várias ferramentas para decodificar o token:

#### Opção A: Usando jwt.io (Online)
1. Acesse: https://jwt.io
2. Cole o token no campo "Encoded"
3. Verifique o payload decodificado na seção "Payload"

#### Opção B: Usando linha de comando (jq + base64)

```bash
# Extrair o payload do token (parte entre os pontos)
TOKEN="seu_token_aqui"
PAYLOAD=$(echo $TOKEN | cut -d. -f2)

# Adicionar padding se necessário
PADDING=$((${#PAYLOAD} % 4))
if [ $PADDING -ne 0 ]; then
    PAYLOAD="${PAYLOAD}$(printf '%*s' $((4 - $PADDING)) | tr ' ' '=')"
fi

# Decodificar e formatar JSON
echo $PAYLOAD | base64 -d 2>/dev/null | jq .
```

#### Opção C: Usando PHP no container

```bash
docker exec -it api_piuba_pescados_app php artisan tinker
```

No tinker:
```php
use Tymon\JWTAuth\Facades\JWTAuth;

$token = 'seu_token_aqui';
$payload = JWTAuth::setToken($token)->getPayload()->toArray();
print_r($payload);
```

### 3. Verificar Claims do Token

O payload do token deve conter:

```json
{
  "iss": "...",
  "iat": 1234567890,
  "exp": 1234571490,
  "nbf": 1234567890,
  "sub": "user-uuid-here",
  "jti": "...",
  "roles": ["master_admin"],
  "is_master_admin": true
}
```

**Claims importantes:**
- `roles`: Array com os nomes das roles do usuário
- `is_master_admin`: Boolean indicando se o usuário é master_admin

### 4. Testar com Diferentes Roles

#### Criar usuário com role específica

```bash
docker exec -it api_piuba_pescados_app php artisan tinker
```

```php
use App\Domain\Models\User;
use App\Domain\Models\Role;
use Illuminate\Support\Facades\Hash;

// Criar role
$role = Role::firstOrCreate(['name' => 'operator']);

// Criar usuário
$user = User::create([
    'name' => 'Test User',
    'email' => 'operator@test.com',
    'password' => Hash::make('password123'),
]);

// Atribuir role
$user->roles()->attach($role->id);

// Fazer login e verificar token
$token = auth('api')->login($user);
$payload = \Tymon\JWTAuth\Facades\JWTAuth::setToken($token)->getPayload()->toArray();
print_r($payload);
```

### 5. Testar Acesso a Rotas Protegidas

```bash
# Obter token primeiro (use o token do passo 1)
TOKEN="seu_token_aqui"

# Acessar rota protegida
curl -X GET http://localhost:8005/api/ping \
  -H "Authorization: Bearer $TOKEN"
```

**Resposta esperada:**
```
pong
```

## 📋 Checklist de Validação

- [ ] Token JWT é gerado com sucesso no login
- [ ] Token contém a claim `roles` como array
- [ ] Token contém a claim `is_master_admin` como boolean
- [ ] Roles corretas aparecem no array `roles`
- [ ] `is_master_admin` é `true` quando usuário tem role `master_admin`
- [ ] `is_master_admin` é `false` quando usuário não tem role `master_admin`
- [ ] Token pode ser usado para autenticar em rotas protegidas
- [ ] Usuário sem roles tem array `roles` vazio

## 🐛 Troubleshooting

### Token não contém roles

1. Verifique se o usuário tem roles atribuídas:
```php
$user = User::find('user-id');
$user->roles; // Deve retornar collection com roles
```

2. Verifique se o método `getJWTCustomClaims()` está sendo chamado:
```php
$user = User::find('user-id');
$claims = $user->getJWTCustomClaims();
print_r($claims);
```

### Erro ao decodificar token

- Verifique se o token está completo (3 partes separadas por ponto)
- Verifique se o `JWT_SECRET` está configurado corretamente no `.env`
- Tente gerar um novo token fazendo login novamente

## 🔐 Segurança

⚠️ **Importante:**
- Sempre valide roles no backend também
- O frontend deve usar essas informações apenas para controle de UI
- Nunca confie apenas no token para autorização crítica
- Tokens expiram automaticamente (verificar `exp` claim)







