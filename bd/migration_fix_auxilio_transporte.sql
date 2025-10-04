-- Migration: Asegurar columna auxilio_transporte como DECIMAL y actualizar valores
-- Safe migration: crea columna temporal, rellena valores calculados, luego reemplaza la columna antigua
-- Threshold: 2 * SMLV (SMLV = 1,423,500 -> 2*SMLV = 2,847,000)

START TRANSACTION;

-- 1) Crear columna temporal si no existe
ALTER TABLE empleados ADD COLUMN IF NOT EXISTS auxilio_transporte_new DECIMAL(10,2) DEFAULT 0.00;

-- 2) Rellenar auxilio_transporte_new según la regla: tipo_contrato = 'LAB' AND mesada > 0 AND mesada < 2*SMLV
UPDATE empleados
SET auxilio_transporte_new = CASE
    WHEN tipo_contrato = 'LAB' AND COALESCE(mesada,0) > 0 AND COALESCE(mesada,0) < 2847000 THEN 200000.00
    ELSE 0.00
END;

-- 3) Hacer backup del valor antiguo en caso de que exista (opcional)
-- Añadimos columna de respaldo si no existe
ALTER TABLE empleados ADD COLUMN IF NOT EXISTS auxilio_transporte_old VARCHAR(64) NULL;
-- Copiar valor antiguo (si la columna existía y era ENUM/otro tipo, se guarda como string)
UPDATE empleados SET auxilio_transporte_old = CAST(COALESCE(auxilio_transporte, '') AS CHAR(64));

-- 4) Reemplazar la columna antigua por la nueva (DROP IF EXISTS + CHANGE)
ALTER TABLE empleados DROP COLUMN IF EXISTS auxilio_transporte;
ALTER TABLE empleados CHANGE auxilio_transporte_new auxilio_transporte DECIMAL(10,2) DEFAULT 0.00;

COMMIT;

-- Nota:
-- 1) Este script asume MySQL 8+ donde "ADD COLUMN IF NOT EXISTS" y "DROP COLUMN IF EXISTS" están disponibles.
-- 2) Hacer un backup completo antes de ejecutar: mysqldump -u root -p rrhh_personal > backup.sql
-- 3) Después de ejecutar, puedes verificar con:
--    SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='empleados' AND COLUMN_NAME='auxilio_transporte';
-- 4) Para recalcular on-demand (si prefieres no tocar datos históricos), puedes usar la actualización de filas a posteriori:
--    UPDATE empleados SET auxilio_transporte = CASE WHEN tipo_contrato='LAB' AND COALESCE(mesada,0)>0 AND COALESCE(mesada,0)<2847000 THEN 200000.00 ELSE 0 END;
