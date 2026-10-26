# Tambah/papar tetapan AI Gateway dalam .env tanpa mendedahkan kunci.
#
#   pwsh -File skrip\tetapan_ai.ps1                 # tunjuk status
#   pwsh -File skrip\tetapan_ai.ps1 -Aktifkan       # set AI_GATEWAY_LIVE=true
#   pwsh -File skrip\tetapan_ai.ps1 -Nyahaktifkan   # set AI_GATEWAY_LIVE=false
#
# Kunci API mesti dimasukkan sendiri oleh pengguna — skrip ini tidak menerima
# kunci sebagai parameter supaya ia tidak tersimpan dalam sejarah shell.

param(
    [switch]$Aktifkan,
    [switch]$Nyahaktifkan
)

$root = Split-Path -Parent $PSScriptRoot
$envPath = Join-Path $root '.env'
$lines = Get-Content $envPath

function Baca-Kunci([string]$k) {
    $baris = $lines | Where-Object { $_ -match "^\s*$k\s*=" } | Select-Object -First 1
    if (-not $baris) { return $null }
    return ($baris -replace "^\s*$k\s*=\s*", '').Trim()
}

$live = Baca-Kunci 'AI_GATEWAY_LIVE'
$url = Baca-Kunci 'AI_GATEWAY_URL'
$key = Baca-Kunci 'AI_GATEWAY_KEY'

$keyAda = $key -and $key -notmatch '^\s*$' -and $key -ne 'null'

if ($Aktifkan) {
    if (-not $keyAda) {
        Write-Host 'RALAT: AI_GATEWAY_KEY belum diisi dalam .env.' -ForegroundColor Red
        Write-Host 'Tambahkan baris berikut secara manual (jangan kongsi kunci ini):' -ForegroundColor Yellow
        Write-Host '  AI_GATEWAY_KEY=asai_sk_...' -ForegroundColor Yellow
        exit 1
    }
    $live = 'true'
}
elseif ($Nyahaktifkan) {
    $live = 'false'
}

if ($Aktifkan -or $Nyahaktifkan) {
    Copy-Item $envPath "$envPath.bak-$(Get-Date -Format 'yyyyMMdd-HHmmss')" -Force

    $adaBaris = $lines | Where-Object { $_ -match '^\s*AI_GATEWAY_LIVE\s*=' }
    if ($adaBaris) {
        $lines = $lines | ForEach-Object { if ($_ -match '^\s*AI_GATEWAY_LIVE\s*=') { "AI_GATEWAY_LIVE=$live" } else { $_ } }
    }
    else {
        $lines += "AI_GATEWAY_LIVE=$live"
    }
    $lines | Set-Content $envPath -Encoding utf8
}

Write-Host ''
Write-Host '=== Status AI Gateway ===' -ForegroundColor Cyan
Write-Host ("  AI_GATEWAY_LIVE : {0}" -f ($(if ($live) { $live } else { '(tidak ditetapkan — lalai false)' })))
Write-Host ("  AI_GATEWAY_URL  : {0}" -f ($(if ($url) { $url } else { '(lalai https://serveras.click/v1)' })))
Write-Host ("  AI_GATEWAY_KEY  : {0}" -f ($(if ($keyAda) { 'ADA (disembunyikan)' } else { 'TIADA' })))
Write-Host ''
Write-Host 'Selepas mengubah .env, jalankan:' -ForegroundColor Cyan
Write-Host '  php artisan config:clear'
Write-Host '  php artisan test --filter=AiGatewayLiveTest'
