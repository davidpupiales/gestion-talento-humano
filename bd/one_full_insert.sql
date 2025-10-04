USE rrhh_personal;

INSERT INTO empleados (
  cedula, nombre_completo, apellido_completo, email, telefono, direccion, fecha_nacimiento, genero, grupo_sanguineo, contacto_emergencia,
  codigo, tipo_contrato, estado, fecha_ingreso, fecha_vencimiento_registro, sede, cargo, nivel, calidad, programa, area, departamento, municipio, servicio, nivel_riesgo,
  dias_trabajados, salario, valor_por_evento, extras_legales, auxilio_transporte, pres_mensual, pres_anual, banco, numero_cuenta, tasa_arl,
  ap_salud_mes, ap_pension_mes, ap_arl_mes, ap_caja_mes, ap_sena_mes, ap_icbf_mes, ap_cesantia_anual, ap_interes_cesantias_anual, ap_prima_anual, poliza,
  mesada, fecha_fin_zo_ingreso, fecha_fin_zo_egreso,
  vigencia_soporte_vital_avanzado, vigencia_victimas_violencia_sexual, vigencia_soporte_vital_basico, vigencia_manejo_dolor_cuidados_paliativos,
  vigencia_humanizacion, vigencia_toma_muestras_lab, vigencia_manejo_duelo, vigencia_manejo_residuos, vigencia_seguridad_vial, vigencia_vigiflow
) VALUES (
  '9999999999', 'Carlos', 'Perez', 'carlos.perez.unique@example.com', '3000000000', 'Calle Falsa 123', '1990-01-01', 'MASCULINO', 'O+', 'Ana Perez 3001112222',
  'EMP-9999', 'LAB', 'ACTIVO', '2025-09-01', '2026-09-01', 'Central', 'Desarrollador', 'OPERATIVO TÉCNICO/ADMINISTRATIVO', 'ADMINISTRATIVO (EN SALUD)', 'PROGRAMA ARTRITIS', 'ADMINISTRATIVO', 'NARIÑO', 'PASTO', 'ATENCIÓN A PACIENTE', 'II',
  30, 2000000.00, 1000.00, 100.00, 200000.00, 2200000.00, 26400000.00, 'Bancolombia', '1234567890', 0.00522,
  200000.00, 160000.00, 10000.00, 5000.00, 1000.00, 1000.00, 240000.00, 2400.00, 240000.00, 'TIENE',
  2000000.00, '2025-10-01', '2026-10-01',
  '2026-01-01', '2026-01-01', '2026-01-01', '2026-01-01',
  '2026-01-01', '2026-01-01', '2026-01-01', '2026-01-01', '2026-01-01', '2026-01-01'
);
