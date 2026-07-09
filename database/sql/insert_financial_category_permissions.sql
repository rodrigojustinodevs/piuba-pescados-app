-- =============================================================================
-- Permissões: financial-category (categorias financeiras)
-- Tabela: permissions (id UUID, name, created_at, updated_at)
-- =============================================================================
-- MySQL / MariaDB — idempotente (não duplica se `name` já existir).
-- PostgreSQL: troque `FROM DUAL` por nada e ajuste literais UUID se necessário.
-- =============================================================================

INSERT INTO permissions (id, name, created_at, updated_at)
SELECT 'c3d4e5f6-a7b8-4c03-9c03-000000000001', 'create-financial-category', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'create-financial-category');

INSERT INTO permissions (id, name, created_at, updated_at)
SELECT 'c3d4e5f6-a7b8-4c03-9c03-000000000002', 'view-financial-category', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'view-financial-category');

INSERT INTO permissions (id, name, created_at, updated_at)
SELECT 'c3d4e5f6-a7b8-4c03-9c03-000000000003', 'update-financial-category', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'update-financial-category');

INSERT INTO permissions (id, name, created_at, updated_at)
SELECT 'c3d4e5f6-a7b8-4c03-9c03-000000000004', 'delete-financial-category', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'delete-financial-category');
