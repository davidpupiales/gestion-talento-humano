-- Migration: Add UNIQUE indexes for cedula and email in empleados
-- Idempotent: creates indexes only if they do not already exist
DELIMITER //
DROP PROCEDURE IF EXISTS add_unique_indexes_empleados//
CREATE PROCEDURE add_unique_indexes_empleados()
BEGIN
  -- Añadir índice único para cedula si no existe
  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'empleados' AND INDEX_NAME = 'ux_empleados_cedula'
  ) THEN
    ALTER TABLE empleados ADD UNIQUE INDEX ux_empleados_cedula (cedula);
  END IF;

  -- Añadir índice único para email si no existe
  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'empleados' AND INDEX_NAME = 'ux_empleados_email'
  ) THEN
    ALTER TABLE empleados ADD UNIQUE INDEX ux_empleados_email (email);
  END IF;
END//
CALL add_unique_indexes_empleados();//
DROP PROCEDURE IF EXISTS add_unique_indexes_empleados//
DELIMITER ;

-- Nota: si existen valores duplicados en cedula o email, el ALTER TABLE lanzará un error.
-- En ese caso, inspeccione los duplicados y resuélvalos manualmente antes de volver a aplicar.
