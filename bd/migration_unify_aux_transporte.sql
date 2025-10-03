-- Migración idempotente: Unificar columnas aux_transporte -> auxilio_transporte
-- Si existe aux_transporte y no existe auxilio_transporte, renombrarla; si ambas existen,
-- eliminaremos aux_transporte (asumiendo que auxilio_transporte es la canónica).

SET @table := 'empleados';
SET @col_old := 'aux_transporte';
SET @col_new := 'auxilio_transporte';

-- 1) Si no existe la columna nueva pero existe la antigua, renombrar
SELECT COUNT(*) INTO @has_new FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @table AND COLUMN_NAME = @col_new;
SELECT COUNT(*) INTO @has_old FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @table AND COLUMN_NAME = @col_old;

-- Si existe old y no existe new -> rename
SET @s = CONCAT('ALTER TABLE ', @table, ' CHANGE ', @col_old, ' ', @col_new, ' DECIMAL(10,2) DEFAULT 0.00;');
-- Ejecutar condicionalmente
PREPARE stmt FROM @s;
IF @has_old = 1 AND @has_new = 0 THEN
  EXECUTE stmt;
END IF;
DEALLOCATE PREPARE stmt;

-- Si existen ambas columnas, eliminar la antigua
IF @has_old = 1 AND @has_new = 1 THEN
  SET @d = CONCAT('ALTER TABLE ', @table, ' DROP COLUMN ', @col_old, ';');
  PREPARE st2 FROM @d;
  EXECUTE st2;
  DEALLOCATE PREPARE st2;
END IF;

-- Fin migración de unificación
