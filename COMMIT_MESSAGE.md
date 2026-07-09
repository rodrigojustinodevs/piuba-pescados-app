fix: corrige retorno de entryDate no endpoint GET de batche

Corrige problemas relacionados ao campo entryDate não ser retornado
corretamente no endpoint GET /company/batche/{id} e erro ao criar batche
via POST quando entry_date é string.

Alterações:
- Atualiza modelo Batche para usar $casts em vez de $dates (depreciado)
- Melhora método formatEntryDate no BatcheMapper para tratar todos os
  tipos de data (Carbon, string, DateTimeInterface)
- Adiciona carregamento do relacionamento tank no método showBatche do
  repositório para evitar erro ao acessar dados do tanque
- Adiciona refresh após criação no repositório para garantir conversão
  correta dos atributos

Correções:
- Erro "Call to a member function toDateString() on string" no POST
- Campo entryDate retornando null no GET mesmo quando existe no banco

Arquivos alterados:
- app/Domain/Models/Batche.php
- app/Infrastructure/Mappers/BatcheMapper.php
- app/Infrastructure/Persistence/BatcheRepository.php