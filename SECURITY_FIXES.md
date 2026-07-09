# Correções de Segurança - Vulnerabilidades Encontradas

## Vulnerabilidades Identificadas

### 1. phpunit/phpunit (CVE-2026-24765) - Severidade: ALTA
- **Versão atual**: 11.5.3
- **Versão vulnerável**: >=11.0.0,<11.5.50
- **Versão segura**: >=11.5.50
- **Dependência transitiva de**: pestphp/pest

### 2. psy/psysh (CVE-2026-25129) - Severidade: MÉDIA
- **Versão atual**: v0.12.7
- **Versão vulnerável**: <=0.12.18
- **Versão segura**: >0.12.18 (>=0.12.19)
- **Dependência transitiva de**: laravel/tinker

## Solução Aplicada

As dependências vulneráveis foram adicionadas diretamente ao `composer.json` com versões seguras:
- `phpunit/phpunit: ^11.5.50` (adicionado em require-dev)
- `psy/psysh: ^0.12.19` (adicionado em require-dev)

## Próximos Passos

Execute o seguinte comando para atualizar as dependências:

```bash
composer update --with-all-dependencies
```

Isso irá:
1. Atualizar o PHPUnit para uma versão >=11.5.50 (corrigindo CVE-2026-24765)
2. Atualizar o PsySH para uma versão >=0.12.19 (corrigindo CVE-2026-25129)

## Verificação

Após a atualização, execute o audit novamente para verificar:

```bash
composer audit --abandoned=ignore
```

## Notas

- O PHPUnit é uma dependência transitiva do Pest, então ao atualizar o PHPUnit, pode ser necessário atualizar o Pest também se houver conflitos.
- O PsySH é uma dependência transitiva do Laravel Tinker. Verifique se há uma versão mais recente do Tinker que suporte uma versão segura do PsySH.
