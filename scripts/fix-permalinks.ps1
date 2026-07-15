$ErrorActionPreference = "Stop"

$Root = Split-Path -Parent $PSScriptRoot
$WpDir = Join-Path $Root "wordpress"
$Htaccess = Join-Path $WpDir ".htaccess"
$Template = Join-Path $PSScriptRoot "wordpress.htaccess"
$WpCli = Join-Path $Root "wp-cli.phar"
$LaragonVhost = "E:\laragon\etc\apache2\sites-enabled\auto.wp-blogs.test.conf"
$VhostTemplate = Join-Path $PSScriptRoot "laragon-wp-blogs.test.conf"

Write-Host "==> Ensuring WordPress .htaccess exists..."
Copy-Item -Path $Template -Destination $Htaccess -Force

Write-Host "==> Flushing WordPress rewrite rules..."
Set-Location $WpDir
php -d display_errors=0 -d error_reporting=0 $WpCli rewrite flush | Out-Null

if (Test-Path "E:\laragon\etc\apache2\sites-enabled") {
    if (-not (Test-Path $LaragonVhost)) {
        Write-Host "==> Creating Laragon virtual host for wp-blogs.test..."
        Copy-Item -Path $VhostTemplate -Destination $LaragonVhost -Force
        Write-Host "    Created: $LaragonVhost"
        Write-Host "    Reload Apache in Laragon: Menu -> Apache -> Reload"
    }
}

Write-Host ""
Write-Host "Permalinks fixed."
Write-Host "Test: http://wp-blogs.test/3-questions-that-changed-how-i-see-my-life/"
