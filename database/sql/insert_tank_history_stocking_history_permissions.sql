-- =============================================================================
-- Permissões: tank-history e stocking-history
-- Tabela: permissions (id UUID, name, created_at, updated_at)
-- =============================================================================
-- MySQL / MariaDB — idempotente (não duplica se `name` já existir).
-- =============================================================================

INSERT INTO permissions (id, name, created_at, updated_at)
SELECT 'c3d4e5f6-a7b8-4c03-9c03-000000000001', 'create-tank-history', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'create-tank-history');

INSERT INTO permissions (id, name, created_at, updated_at)
SELECT 'c3d4e5f6-a7b8-4c03-9c03-000000000002', 'view-tank-history', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'view-tank-history');

INSERT INTO permissions (id, name, created_at, updated_at)
SELECT 'd4e5f6a7-b8c9-4d04-9d04-000000000001', 'create-stocking-history', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'create-stocking-history');

INSERT INTO permissions (id, name, created_at, updated_at)
SELECT 'd4e5f6a7-b8c9-4d04-9d04-000000000002', 'view-stocking-history', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'view-stocking-history');
