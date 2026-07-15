$ErrorActionPreference = "Stop"

$Root = Split-Path -Parent $PSScriptRoot
$LaragonWww = "E:\laragon\www\wp-blogs"
$WordPressDir = Join-Path $Root "wordpress"

if (-not (Test-Path $WordPressDir)) {
    throw "WordPress directory not found: $WordPressDir"
}

if (Test-Path $LaragonWww) {
    Write-Host "Laragon link already exists: $LaragonWww"
} else {
    cmd /c mklink /J "$LaragonWww" "$WordPressDir" | Out-Null
    if ($LASTEXITCODE -ne 0) {
        throw "Failed to create junction. Run this script as Administrator or create the link manually."
    }
    Write-Host "Created junction: $LaragonWww -> $WordPressDir"
}

$hostsPath = "$env:SystemRoot\System32\drivers\etc\hosts"
$hostsEntry = "127.0.0.1 wp-blogs.test"
$hosts = Get-Content $hostsPath -ErrorAction SilentlyContinue
if ($hosts -notcontains $hostsEntry) {
    try {
        Add-Content -Path $hostsPath -Value $hostsEntry
        Write-Host "Added hosts entry: $hostsEntry"
    } catch {
        Write-Warning "Could not update hosts file automatically. Add this line manually: $hostsEntry"
    }
} else {
    Write-Host "Hosts entry already present."
}

Write-Host ""
Write-Host "Done. Start Laragon (Apache + MySQL), then open http://wp-blogs.test"
