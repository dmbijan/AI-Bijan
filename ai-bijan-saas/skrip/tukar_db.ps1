# Tukar DB_SQLITE → MySQL (atau sebaliknya) dengan selamat.
#
#   pwsh -File skrip\tukar_db.ps1 -Ke mysql -Host 127.0.0.1 -Database ai_bijan -User root -Password ""
#   pwsh -File skrip\tukar_db.ps1 -Ke sqlite
#
# Skrip ini menyandarkan .env sebelum mengubah dan TIDAK memulakan servis MySQL.

param(
    [Parameter(Mandatory = $true)][ValidateSet('mysql', 'sqlite')][string]$Ke,
    [string]$Host = '127.0.0.1',
    [string]$Database = 'ai_bijan',
    [string]$User = 'root',
    [string]$Password = ''
)

$root = Split-Path -Parent $PSScriptRoot
$envPath = Join-Path $root '.env'

if (-not (Test-Path $envPath)) {
    throw "Fail .env tidak dijumpai di $envPath"
}

Copy-Item $envPath "$envPath.bak-$(Get-Date -Format 'yyyyMMdd-HHmmss')" -Force

$lines = Get-Content $envPath

function Set-EnvKey {
    param([string[]]$Lines, [string]$Key, [string]$Value)
    $found = $false
    $out = foreach ($l in $Lines) {
        if ($l -match "^\s*#?\s*$Key\s*=") {
            $found = $true
            "$Key=$Value"
        }
        else { $l }
    }
    if (-not $found) { $out += "$Key=$Value" }
    return $out
}

if ($Ke -eq 'mysql') {
    # Semak sambungan sebelum menulis .env.
    $php = @"
try {
    new PDO('mysql:host=$Host;port=3306;dbname=$Database', '$User', '$Password');
    echo 'OK';
} catch (Throwable \$e) {
    echo 'GAGAL: ' . \$e->getMessage();
}
"@
    $tmp = Join-Path $root 'storage\cek_db.php'
    Set-Content -Path $tmp -Value "<?php $php" -Encoding utf8
    $hasil = & php $tmp 2>&1
    Remove-Item $tmp -Force

    if ($hasil -notmatch '^OK') {
        throw "Sambungan MySQL gagal — .env tidak diubah. Butiran: $hasil"
    }

    $lines = Set-EnvKey $lines 'DB_CONNECTION' 'mysql'
    $lines = Set-EnvKey $lines 'DB_HOST' $Host
    $lines = Set-EnvKey $lines 'DB_PORT' '3306'
    $lines = Set-EnvKey $lines 'DB_DATABASE' $Database
    $lines = Set-EnvKey $lines 'DB_USERNAME' $User
    $lines = Set-EnvKey $lines 'DB_PASSWORD' $Password
}
else {
    $lines = Set-EnvKey $lines 'DB_CONNECTION' 'sqlite'
}

$lines | Set-Content $envPath -Encoding utf8

Write-Host "Selesai: DB_CONNECTION=$Ke" -ForegroundColor Green
Write-Host 'Langkah seterusnya:' -ForegroundColor Cyan
if ($Ke -eq 'mysql') {
    Write-Host '  php artisan config:clear'
    Write-Host '  php artisan migrate:fresh --seed'
    Write-Host '  php artisan test'
}
else {
    Write-Host '  php artisan config:clear'
    Write-Host '  php artisan test'
}
