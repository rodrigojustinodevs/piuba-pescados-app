# Piuba Pescados API

API REST para o sistema de gestão de piscicultura Piuba Pescados, desenvolvida em Laravel 12 com arquitetura em camadas.

## 📋 Pré-requisitos

- Docker e Docker Compose instalados
- Git
- Portas disponíveis: `8005` (nginx), `3308` (MySQL), `1883` e `9001` (MQTT)

## 🚀 Instalação

### 1. Clone o repositório

```bash
git clone <url-do-repositorio>
cd piuba-pescados-app
```

### 2. Configure o ambiente

Crie um arquivo `.env` baseado no `.env.example` (se existir) ou configure as seguintes variáveis:

```env
APP_NAME="Piuba Pescados API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8005

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=piuba_pescados
DB_USERNAME=piuba_user
DB_PASSWORD=piuba_password

JWT_SECRET=
JWT_TTL=60
JWT_REFRESH_TTL=20160

REDIS_HOST=redis
QUEUE_CONNECTION=redis
```

### 3. Gere a chave da aplicação e JWT

```bash
# Dentro do container Docker
docker exec -it api_piuba_pescados_app php artisan key:generate
docker exec -it api_piuba_pescados_app php artisan jwt:secret
```

### 4. Construa e inicie os containers

```bash
docker-compose up -d --build
```

### 5. Execute as migrations

```bash
docker exec -it api_piuba_pescados_app php artisan migrate
```

### 6. Execute os seeders

```bash
docker exec -it api_piuba_pescados_app php artisan db:seed
```

Os seeders irão criar:
- Roles: `operator`, `master_admin`, `company_admin`, `manager`
- Permissões para todas as entidades
- Companies de teste
- Usuário master_admin (email: `master.admin@piuba.com`, senha: `password123`)

## 🔧 Comandos Úteis

### Acessar o container da aplicação

```bash
docker exec -it api_piuba_pescados_app bash
```

### Executar comandos Artisan

```bash
docker exec -it api_piuba_pescados_app php artisan <comando>
```

### Ver logs

```bash
docker logs api_piuba_pescados_app -f
```

### Parar os containers

```bash
docker-compose down
```

### Parar e remover volumes (⚠️ apaga dados do banco)

```bash
docker-compose down -v
```

## 📚 Estrutura do Projeto

O projeto segue uma arquitetura em camadas:

```
app/
├── Application/        # Camada de aplicação (DTOs, Services, UseCases)
├── Domain/            # Camada de domínio (Models, Repositories, Enums)
├── Infrastructure/    # Camada de infraestrutura (Persistence, Providers)
└── Presentation/      # Camada de apresentação (Controllers, Requests, Resources, Middleware)
```

## 🔐 Autenticação

A API utiliza autenticação JWT. Para obter um token:

```bash
POST /api/login
Content-Type: application/json

{
  "email": "master.admin@piuba.com",
  "password": "password123"
}
```

Resposta:
```json
{
  "status": true,
  "response": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  },
  "message": "Authenticated successfully"
}
```

Use o token nas requisições subsequentes:

```
Authorization: Bearer {token}
```

## 👥 Roles e Permissões

### Roles Disponíveis

- **master_admin**: Administrador master do sistema
- **company_admin**: Administrador de company
- **manager**: Gerente
- **operator**: Operador

### Permissões

As permissões seguem o padrão: `{acao}-{entidade}`

Exemplos:
- `create-company`
- `update-company`
- `delete-company`
- `view-company`
- `create-tank`
- `update-tank`
- etc.

## 🧪 Testes

```bash
docker exec -it api_piuba_pescados_app php artisan test
```

## 📖 Documentação da API

A documentação Swagger/OpenAPI está disponível em:

```
http://localhost:8005/api/docs
```

Para gerar/atualizar a documentação:

```bash
docker exec -it api_piuba_pescados_app php artisan l5-swagger:generate
```

## 🛠️ Tecnologias

- **Laravel 12**: Framework PHP
- **PHP 8.3**: Linguagem de programação
- **MySQL 8.0**: Banco de dados
- **Redis**: Cache e filas
- **JWT Auth**: Autenticação
- **Docker**: Containerização
- **Nginx**: Servidor web

## 📝 Variáveis de Ambiente Importantes

| Variável | Descrição | Padrão |
|----------|-----------|--------|
| `APP_URL` | URL base da aplicação | `http://localhost:8005` |
| `DB_DATABASE` | Nome do banco de dados | `piuba_pescados` |
| `DB_USERNAME` | Usuário do banco | `piuba_user` |
| `DB_PASSWORD` | Senha do banco | `piuba_password` |
| `JWT_SECRET` | Chave secreta do JWT | (gerado) |
| `JWT_TTL` | Tempo de vida do token (minutos) | `60` |

## 🐛 Troubleshooting

### Erro ao conectar ao banco de dados

Verifique se o container MySQL está rodando:

```bash
docker ps | grep mysql
```

### Erro de permissões no storage

```bash
docker exec -it api_piuba_pescados_app chmod -R 775 storage bootstrap/cache
```

### Limpar cache

```bash
docker exec -it api_piuba_pescados_app php artisan optimize:clear
```

### Reinstalar dependências

```bash
docker exec -it api_piuba_pescados_app composer install
```

## 📄 Licença

Este projeto é proprietário e confidencial.

## 👨‍💻 Desenvolvimento

Para desenvolvimento local, você pode usar:

```bash
composer dev
```

Isso iniciará:
- Servidor Laravel
- Queue worker
- Log viewer (Pail)
- Vite (frontend assets)

## 🔗 Endpoints Principais

- `POST /api/login` - Autenticação
- `GET /api/ping` - Health check (requer autenticação)
- `GET /api/docs` - Documentação Swagger

Para mais endpoints, consulte a documentação Swagger em `/api/docs`.

## 👥 Usuários de Teste por Role

O seeder `UsersByRoleSeeder` cria um usuário para cada role, permitindo testar os diferentes tipos de acesso.

### Usuários Criados

| Role | Email | Senha | Descrição |
|------|-------|-------|-----------|
| `admin` | `admin@piuba.com` | `password123` | Administrador geral |
| `master_admin` | `master.admin@piuba.com` | `password123` | Administrador master |
| `company_admin` | `company.admin@piuba.com` | `password123` | Administrador de company |
| `company-admin` | `company-admin@piuba.com` | `password123` | Administrador de company (com hífen) |
| `manager` | `manager@piuba.com` | `password123` | Gerente |
| `operator` | `operator@piuba.com` | `password123` | Operador |
| `guest` | `guest@piuba.com` | `password123` | Convidado |

### O que o Seeder Faz

O seeder realiza as seguintes ações para cada usuário:

| Ação | Descrição |
|------|-----------|
| Cria/Atualiza usuário | Cria ou atualiza o usuário com as credenciais especificadas |
| Associa role globalmente | Associa o role ao usuário na tabela `role_user` |
| Vincula à company | Vincula o usuário a uma company na tabela `company_user` |
| Associa role na company | Associa o role do usuário na company (tabela `company_user_role`) |
| Define is_admin | Define `is_admin = true` para roles `admin` e `master_admin` |