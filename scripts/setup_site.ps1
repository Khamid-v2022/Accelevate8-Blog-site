$ErrorActionPreference = "Stop"

$Root = Split-Path -Parent $PSScriptRoot
$WpDir = Join-Path $Root "wordpress"
$WpCli = @("php", (Join-Path $Root "wp-cli.phar"))

function wp_output($text) {
    $lines = $text -split "`n" | Where-Object { $_ -notmatch '^(Deprecated:|Warning:)' -and $_.Trim() -ne '' }
    return ($lines -join "`n").Trim()
}

function Invoke-Wp {
    param(
        [Parameter(ValueFromRemainingArguments = $true)][string[]]$WpArgs,
        [switch]$Quiet
    )
    $prev = $ErrorActionPreference
    $ErrorActionPreference = "Continue"
    try {
        $output = & php -d display_errors=0 -d error_reporting=0 $WpCli[1] @WpArgs 2>&1
        $exit = $LASTEXITCODE
    } finally {
        $ErrorActionPreference = $prev
    }

    $clean = @($output | Where-Object {
        $_ -notmatch '^(Deprecated:|Warning:)' -and "$_".Trim() -ne ''
    })

    if (-not $Quiet) {
        $clean | ForEach-Object { Write-Host $_ }
    }
    if ($exit -ne 0) {
        $joined = $WpArgs -join ' '
        if ($joined -match 'plugin activate' -and ($output -join ' ') -match 'already active') {
            return $clean
        }
        throw "WP-CLI failed: $joined`n$($output -join "`n")"
    }
    return $clean
}

Set-Location $WpDir

Write-Host "==> Activating plugins..."
Invoke-Wp plugin activate mindful-living kadence-blocks

Write-Host "==> Configuring permalinks..."
Invoke-Wp rewrite structure "/%postname%/" --hard
Copy-Item -Path (Join-Path $Root "scripts\wordpress.htaccess") -Destination (Join-Path $WpDir ".htaccess") -Force

Write-Host "==> Creating pages..."
Invoke-Wp eval-file (Join-Path $Root "scripts\setup-homepage.php") | Out-Null

$homeId = Invoke-Wp post list --post_type=page --name=home --field=ID
$blogId = Invoke-Wp post list --post_type=page --name=blog --field=ID

Write-Host "==> Site tagline..."
Invoke-Wp option update blogdescription "Gentle reads on goals, habits, mindset, and reflection"

Write-Host "==> Importing posts from docx..."
Set-Location $Root
python (Join-Path $Root "scripts\import_posts.py")
if ($LASTEXITCODE -ne 0) { throw "Post import failed" }

Set-Location $WpDir

Write-Host "==> Creating company pages..."
Invoke-Wp eval-file (Join-Path $Root "scripts\setup-company-pages.php") | Out-Null

Write-Host "==> Creating navigation menu..."
Invoke-Wp eval-file (Join-Path $Root "scripts\setup-menu.php") | Out-Null

Write-Host "==> Kadence readability settings..."
Invoke-Wp eval-file (Join-Path $Root "scripts\configure-kadence.php") -Quiet | Out-Null

Write-Host "==> Applying Accelevate branding..."
Invoke-Wp eval-file (Join-Path $Root "scripts\setup-brand.php") -Quiet | Out-Null

Write-Host "==> Flushing rewrite rules..."
Invoke-Wp rewrite flush --hard

Write-Host ""
Write-Host "Setup complete!"
Write-Host "  Site:  http://wp-blogs.test"
Write-Host "  Admin: http://wp-blogs.test/wp-admin"
Write-Host "  Login: admin / admin123"
