# Collection Insomnia – Piuba API

Collection com todos os endpoints da API Piuba para uso no [Insomnia](https://insomnia.rest/).

## Como importar

1. Abra o Insomnia.
2. **Application** → **Import/Export** → **Import Data** (ou `Ctrl+O` / `Cmd+O`).
3. Selecione o arquivo `piuba-api-collection.json`.

## Variáveis de ambiente

No environment **Base Environment** (ou crie o seu), configure:

| Variável         | Exemplo                    | Uso |
|------------------|----------------------------|-----|
| `base_url`       | `http://localhost:8005/api` | URL base da API |
| `token`          | `eyJ0eXAiOiJKV1QiLCJh...`   | JWT retornado no Login (Bearer) |
| `company_id`     | `1`                         | ID da company (rotas admin/company e algumas company) |
| `subscription_id`| `1`                         | ID da subscription (rotas admin) |

## Fluxo sugerido

1. **Login** (Auth → Login): envie `email` e `password`; copie o `token` da resposta.
2. Cole o `token` na variável `token` do environment.
3. Use **Ping** para validar o token.
4. Chame os demais endpoints; eles já usam `Authorization: Bearer {{ token }}`.

## Estrutura da collection

- **Auth**: Login, Ping
- **Admin**: Company, Subscription (CRUD)
- **Company (recursos)**: Alert, Batch, Biometry, Client, Cost Allocation, Feeding, Feed Inventory, Financial Category, Financial Transaction, Harvest, Growth Curve, Mortality (+ survival-rate), Purchase, Sale, Sensor, Stocking, Stock, Supplier, Transfer, Tank (+ tank-types, tanks/without-batches), Water Quality

Os bodies dos requests estão em branco (`{}`) ou com exemplo mínimo; preencha conforme os DTOs da API (ex.: `storage/api-docs/api-docs.json` ou documentação do projeto).
