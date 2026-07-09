-- =============================================================================
-- Permissões: sensor-reading e water-quality
-- Tabela: permissions (id UUID, name, created_at, updated_at)
-- =============================================================================
-- MySQL / MariaDB — idempotente (não duplica se `name` já existir).
-- PostgreSQL: troque `FROM DUAL` por nada (ex.: `SELECT ... WHERE NOT EXISTS` sem FROM)
--   e use `::uuid` no literal do id se a coluna for tipo uuid.
-- =============================================================================

INSERT INTO permissions (id, name, created_at, updated_at)
SELECT 'a1b2c3d4-e5f6-4a01-8a01-000000000001', 'create-sensor-reading', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'create-sensor-reading');

INSERT INTO permissions (id, name, created_at, updated_at)
SELECT 'a1b2c3d4-e5f6-4a01-8a01-000000000002', 'view-sensor-reading', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'view-sensor-reading');

INSERT INTO permissions (id, name, created_at, updated_at)
SELECT 'a1b2c3d4-e5f6-4a01-8a01-000000000003', 'update-sensor-reading', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'update-sensor-reading');

INSERT INTO permissions (id, name, created_at, updated_at)
SELECT 'a1b2c3d4-e5f6-4a01-8a01-000000000004', 'delete-sensor-reading', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'delete-sensor-reading');

INSERT INTO permissions (id, name, created_at, updated_at)
SELECT 'b2c3d4e5-f6a7-4b02-9b02-000000000001', 'create-water-quality', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'create-water-quality');

INSERT INTO permissions (id, name, created_at, updated_at)
SELECT 'b2c3d4e5-f6a7-4b02-9b02-000000000002', 'view-water-quality', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'view-water-quality');

INSERT INTO permissions (id, name, created_at, updated_at)
SELECT 'b2c3d4e5-f6a7-4b02-9b02-000000000003', 'update-water-quality', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'update-water-quality');

INSERT INTO permissions (id, name, created_at, updated_at)
SELECT 'b2c3d4e5-f6a7-4b02-9b02-000000000004', 'delete-water-quality', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'delete-water-quality');
