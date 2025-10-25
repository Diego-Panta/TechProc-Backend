# dividir-proyecto.ps1
# Script para dividir proyecto Laravel en partes ZIP compatibles

Write-Host "================================================" -ForegroundColor Cyan
Write-Host "  DIVIDIR PROYECTO EN PARTES ZIP COMPATIBLES" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# CONFIGURACIÓN
$carpetaProyecto = "TechProc-Backend"
$tamanoParte = 10MB  # 10 megabytes por parte
$archivoTemporal = "temp-proyecto.zip"

# Verificar que existe la carpeta
if (!(Test-Path $carpetaProyecto)) {
    Write-Host "ERROR: No se encuentra la carpeta '$carpetaProyecto'" -ForegroundColor Red
    Write-Host "Asegurate de ejecutar este script en el mismo directorio que tu proyecto" -ForegroundColor Yellow
    pause
    exit
}

# Paso 1: Comprimir proyecto completo
Write-Host "[1/3] Comprimiendo proyecto..." -ForegroundColor Green
Write-Host "      Carpeta: $carpetaProyecto" -ForegroundColor Gray

try {
    Compress-Archive -Path $carpetaProyecto -DestinationPath $archivoTemporal -CompressionLevel Optimal -Force
    Write-Host "      ✅ Comprimido exitosamente" -ForegroundColor Green
} catch {
    Write-Host "      ❌ Error al comprimir: $_" -ForegroundColor Red
    pause
    exit
}

$tamanoTotal = (Get-Item $archivoTemporal).Length
Write-Host "      Tamaño: $([math]::Round($tamanoTotal / 1MB, 2)) MB" -ForegroundColor Gray
Write-Host ""

# Paso 2: Dividir en partes
Write-Host "[2/3] Dividiendo en partes de $([math]::Round($tamanoParte / 1MB)) MB..." -ForegroundColor Green

$bytes = [System.IO.File]::ReadAllBytes($archivoTemporal)
$totalPartes = [math]::Ceiling($bytes.Length / $tamanoParte)

Write-Host "      Total de partes a crear: $totalPartes" -ForegroundColor Gray
Write-Host ""

for ($i = 0; $i -lt $totalPartes; $i++) {
    $inicio = $i * $tamanoParte
    $longitud = [math]::Min($tamanoParte, $bytes.Length - $inicio)
    $fin = $inicio + $longitud - 1
    $parte = $bytes[$inicio..$fin]
    
    $nombreParte = "parte" + ($i + 1).ToString("000") + ".zip"
    [System.IO.File]::WriteAllBytes($nombreParte, $parte)
    
    $porcentaje = [math]::Round((($i + 1) / $totalPartes) * 100, 1)
    $tamañoParte = [math]::Round($parte.Length / 1MB, 2)
    
    Write-Host "      [$porcentaje%] ✅ $nombreParte ($tamañoParte MB)" -ForegroundColor Cyan
}

# Paso 3: Limpiar archivo temporal
Write-Host ""
Write-Host "[3/3] Limpiando archivos temporales..." -ForegroundColor Green
Remove-Item $archivoTemporal -Force
Write-Host "      ✅ $archivoTemporal eliminado" -ForegroundColor Green

Write-Host ""
Write-Host "================================================" -ForegroundColor Green
Write-Host "  ✅ PROCESO COMPLETADO EXITOSAMENTE" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Green
Write-Host ""
Write-Host "Archivos creados:" -ForegroundColor Yellow
for ($i = 1; $i -le $totalPartes; $i++) {
    $nombreParte = "parte" + $i.ToString("000") + ".zip"
    Write-Host "  - $nombreParte" -ForegroundColor White
}
Write-Host ""
Write-Host "Siguiente paso:" -ForegroundColor Yellow
Write-Host "  1. Sube TODOS los archivos parte*.zip a tu servidor con FileZilla" -ForegroundColor White
Write-Host "  2. Sube el script descomprimir-partes.php" -ForegroundColor White
Write-Host "  3. Ejecuta el script desde tu navegador" -ForegroundColor White
Write-Host ""

pause