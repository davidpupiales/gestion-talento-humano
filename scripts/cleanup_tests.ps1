<#
scripts/cleanup_tests.ps1

Uso:
  # Dry-run (lista archivos que serían eliminados)
  .\cleanup_tests.ps1

  # Borrar archivos (requiere confirmación adicional)
  .\cleanup_tests.ps1 -Delete

Patrones buscados (seguro): *.tmp, *.log, *.bak, *~, *.old, *.swp, *.cache, *.pid, Thumbs.db
Se excluyen carpetas: .git, node_modules, vendor, app/.next, public, bd (por seguridad)
#>
param(
    [switch]$Delete
)

$repoRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$excludeDirs = @('.git','node_modules','vendor','app\\.next','public','bd')
$patterns = @('*.tmp','*.log','*.bak','*~','*.old','*.swp','*.cache','*.pid','Thumbs.db')

Write-Output "Repositorio: $repoRoot"
$mode = 'DRY-RUN'
if ($Delete) { $mode = 'DELETE' }
Write-Output "Modo: $mode"

# Recurse and find files matching patterns, excluding directories
$found = @()
foreach ($pattern in $patterns) {
    $found += Get-ChildItem -Path $repoRoot -Recurse -Force -File -ErrorAction SilentlyContinue -Include $pattern |
        Where-Object { $file = $_; -not ($excludeDirs | ForEach-Object { $file.FullName -match [regex]::Escape((Join-Path $repoRoot $_)) }) }
}

if (-not $found) {
    Write-Output "No se encontraron archivos temporales con los patrones especificados."
    exit 0
}

Write-Output "Archivos candidatos encontrados:"
$found | Select-Object FullName, Length, LastWriteTime | Format-Table -AutoSize

if ($Delete) {
    Write-Output "\nEliminando archivos..."
    foreach ($f in $found) {
        try {
            Remove-Item -LiteralPath $f.FullName -Force -ErrorAction Stop
            Write-Output "Deleted: $($f.FullName)"
        } catch {
            Write-Warning "Failed to delete $($f.FullName): $_"
        }
    }
    Write-Output "Eliminación completada."
} else {
    Write-Output "\nEjecute con -Delete para eliminar los archivos listados (recomendado revisar primero)."
}
