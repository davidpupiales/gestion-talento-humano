param(
    [string]$dbUser = 'root',
    [string]$dbPass = '123456789',
    [string]$dbName = 'rrhh_personal'
)

# Simple E2E: login + POST + DB check
$ts = (Get-Date -Format 'yyyyMMddHHmmss')
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

# 1) Login
$loginResp = Invoke-WebRequest -Uri 'http://localhost/gestion-talento-humano/login.php' -Method POST -Body @{ usuario='admin'; password='Prueba123!' } -WebSession $session -UseBasicParsing
if ($loginResp.StatusCode -ne 200) { Write-Error "Login request failed: $($loginResp.StatusCode)"; exit 1 }

# 2) POST nuevo empleado
$email = "e2e.test.$ts@example.invalid"
$form = @{
    tipo_contrato='LAB'; codigo='E2E-'+$ts; estado='ACTIVO'; cedula='E2E'+$ts; nombre_completo='E2E Test '+$ts;
    fecha_nacimiento='1990-01-01'; direccion='Calle E2E'; email=$email; telefono='3000000000'; genero='MASCULINO';
    cargo='E2E'; departamento='QA'; municipio='Ciudad'; nivel_riesgo='II'; fecha_inicio='2025-01-01'; fecha_fin='2025-01-31';
    mesada='1000000'; extras_legales='0'; ap_salud_mes='0'; ap_pension_mes='0'; ap_arl_mes='0'; ap_caja_mes='0'; ap_sena_mes='0';
    ap_icbf_mes='0'; ap_cesantia_anual='0'; ap_interes_cesantias_anual='0'; ap_prima_anual='0'; num_cuenta='000000'; entidad_bancaria='E2E Bank'; submit='Guardar'
}

$postResp = Invoke-WebRequest -Uri 'http://localhost/gestion-talento-humano/empleados.php' -Method POST -Body $form -WebSession $session -UseBasicParsing -ErrorAction Stop

# 3) Check DB for inserted email
$query = "SELECT id,codigo,email,mesada,salario FROM empleados WHERE email = '$email' LIMIT 1;"
$mysql = "C:\\xampp\\mysql\\bin\\mysql.exe"
# Ejecutar la consulta enviándola por STDIN para evitar problemas de parsing de PowerShell con -p
$tmpFile = [IO.Path]::GetTempFileName()
Set-Content -Path $tmpFile -Value $query
$out = Get-Content $tmpFile | & $mysql -u $dbUser --password=$dbPass $dbName 2>&1
Remove-Item $tmpFile -Force

if ($out -and $out -match $email) {
    Write-Host "E2E SUCCESS: Employee inserted with email $email"
    Write-Host $out
    exit 0
} else {
    Write-Error "E2E FAIL: Employee not found in DB. Output:"; Write-Host $out; exit 2
}
