param(
    [string]$dbUser = 'root',
    [string]$dbPass = '123456789',
    [string]$dbName = 'rrhh_personal'
)

$mysql = "C:\\xampp\\mysql\\bin\\mysql.exe"

Write-Host "Checking columns in table empleados..."
$q = "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='empleados' AND COLUMN_NAME IN ('aux_transporte','auxilio_transporte');"
$cols = & $mysql -u $dbUser -p$dbPass $dbName -e $q 2>&1 | Select-Object -Skip 1

if ($cols -match 'aux_transporte' -and $cols -notmatch 'auxilio_transporte') {
    Write-Host "Renaming aux_transporte -> auxilio_transporte"
    $alter = "ALTER TABLE empleados CHANGE aux_transporte auxilio_transporte DECIMAL(10,2) DEFAULT 0.00;"
    & $mysql -u $dbUser -p$dbPass $dbName -e $alter
    Write-Host "Renamed."
} elseif ($cols -match 'aux_transporte' -and $cols -match 'auxilio_transporte') {
    Write-Host "Both columns exist: dropping aux_transporte (keeping auxilio_transporte)"
    $alter = "ALTER TABLE empleados DROP COLUMN aux_transporte;"
    & $mysql -u $dbUser -p$dbPass $dbName -e $alter
    Write-Host "Dropped aux_transporte."
} else {
    Write-Host "No action needed: aux_transporte not present or auxilio_transporte already present."
}
