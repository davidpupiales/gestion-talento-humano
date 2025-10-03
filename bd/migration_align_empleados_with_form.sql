-- Migración: Alinear la tabla `empleados` con los campos usados por el formulario
-- Añade columnas faltantes y columnas para vigencias/cálculos usados por el frontend
ALTER TABLE empleados
  ADD COLUMN nombre VARCHAR(255) NULL,
  ADD COLUMN apellido VARCHAR(255) NULL,
  ADD COLUMN mesada DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN pres_mensual DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN pres_anual DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN extras_legales DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN aux_transporte DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN entidad_bancaria VARCHAR(100) NULL,
  ADD COLUMN num_cuenta VARCHAR(50) NULL,
  ADD COLUMN ap_salud_mes DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN ap_pension_mes DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN ap_arl_mes DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN ap_caja_mes DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN ap_sena_mes DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN ap_icbf_mes DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN ap_cesantia_anual DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN ap_interes_cesantias_anual DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN ap_prima_anual DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN valor_por_evento DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN fecha_fin_zo_ingreso DATE NULL,
  ADD COLUMN fecha_fin_zo_egreso DATE NULL,
  ADD COLUMN contacto_emergencia VARCHAR(255) NULL,
  ADD COLUMN fecha_vencimiento_registro DATE NULL,
  ADD COLUMN vigencia_soporte_vital_avanzado DATE NULL,
  ADD COLUMN vigencia_victimas_violencia_sexual DATE NULL,
  ADD COLUMN vigencia_soporte_vital_basico DATE NULL,
  ADD COLUMN vigencia_manejo_dolor_cuidados_paliativos DATE NULL,
  ADD COLUMN vigencia_humanizacion DATE NULL,
  ADD COLUMN vigencia_toma_muestras_lab DATE NULL,
  ADD COLUMN vigencia_manejo_duelo DATE NULL,
  ADD COLUMN vigencia_manejo_residuos DATE NULL,
  ADD COLUMN vigencia_seguridad_vial DATE NULL,
  ADD COLUMN vigencia_vigiflow DATE NULL;

-- Rellenar columnas nombre/apellido basadas en nombre_completo/apellido_completo si existen
UPDATE empleados SET
  nombre = CASE WHEN nombre_completo IS NOT NULL AND nombre_completo != '' THEN SUBSTRING_INDEX(nombre_completo, ' ', 1) ELSE NULL END,
  apellido = CASE WHEN apellido_completo IS NOT NULL AND apellido_completo != '' THEN apellido_completo ELSE NULL END
WHERE 1;

-- Nota: Revisar la columna `salario` (NOT NULL). El formulario usa `mesada`.
-- Si deseas que `salario` refleje `mesada`, ejecutar:
-- UPDATE empleados SET salario = mesada WHERE salario IS NULL OR salario = 0;

-- Fin de la migración
