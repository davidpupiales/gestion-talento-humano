-- Migración idempotente: Alinear la tabla `empleados` con los campos usados por el formulario
-- Usa `ADD COLUMN IF NOT EXISTS` para evitar errores cuando la columna ya existe.
-- Nota: `ADD COLUMN IF NOT EXISTS` requiere MySQL 8.0.16+. Si su versión es anterior,
-- cámbiela por comprobaciones con information_schema.

ALTER TABLE empleados
  ADD COLUMN IF NOT EXISTS nombre VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS apellido VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS mesada DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS pres_mensual DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS pres_anual DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS extras_legales DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS aux_transporte DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS entidad_bancaria VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS num_cuenta VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS ap_salud_mes DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS ap_pension_mes DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS ap_arl_mes DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS ap_caja_mes DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS ap_sena_mes DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS ap_icbf_mes DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS ap_cesantia_anual DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS ap_interes_cesantias_anual DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS ap_prima_anual DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS valor_por_evento DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS fecha_fin_zo_ingreso DATE NULL,
  ADD COLUMN IF NOT EXISTS fecha_fin_zo_egreso DATE NULL,
  ADD COLUMN IF NOT EXISTS contacto_emergencia VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS fecha_vencimiento_registro DATE NULL,
  ADD COLUMN IF NOT EXISTS vigencia_soporte_vital_avanzado DATE NULL,
  ADD COLUMN IF NOT EXISTS vigencia_victimas_violencia_sexual DATE NULL,
  ADD COLUMN IF NOT EXISTS vigencia_soporte_vital_basico DATE NULL,
  ADD COLUMN IF NOT EXISTS vigencia_manejo_dolor_cuidados_paliativos DATE NULL,
  ADD COLUMN IF NOT EXISTS vigencia_humanizacion DATE NULL,
  ADD COLUMN IF NOT EXISTS vigencia_toma_muestras_lab DATE NULL,
  ADD COLUMN IF NOT EXISTS vigencia_manejo_duelo DATE NULL,
  ADD COLUMN IF NOT EXISTS vigencia_manejo_residuos DATE NULL,
  ADD COLUMN IF NOT EXISTS vigencia_seguridad_vial DATE NULL,
  ADD COLUMN IF NOT EXISTS vigencia_vigiflow DATE NULL;

-- Backfill: si no existen, llenar nombre/apellido a partir de nombre_completo/apellido_completo
UPDATE empleados
SET
  nombre = CASE WHEN COALESCE(nombre_completo,'') <> '' THEN SUBSTRING_INDEX(nombre_completo, ' ', 1) ELSE NULL END,
  apellido = CASE WHEN COALESCE(apellido_completo,'') <> '' THEN apellido_completo ELSE NULL END
WHERE 1;

-- Recomendación: si desea sincronizar salario con mesada (cuando mesada es la fuente),
-- puede ejecutar manualmente la siguiente línea después de revisar los datos:
-- UPDATE empleados SET salario = mesada WHERE salario IS NULL OR salario = 0;

-- Fin migración idempotente
