param(
    [string]$Version = ""
)
$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $PSScriptRoot
$Deploy = Join-Path $Root "deploy"
$Dist = Join-Path $Root "dist"
$Bootstrap = Join-Path $Root "app\bootstrap.php"

if (-not $Version) {
    $m = Select-String -Path $Bootstrap -Pattern "APP_VERSION',\s*'([^']+)'" | Select-Object -First 1
    if (-not $m) { throw "APP_VERSION not found in app/bootstrap.php" }
    $Version = $m.Matches[0].Groups[1].Value
}

Remove-Item $Deploy -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item $Dist -Recurse -Force -ErrorAction SilentlyContinue
New-Item -ItemType Directory -Path $Deploy,$Dist | Out-Null

function Copy-Path($relative) {
    $src = Join-Path $Root $relative
    $dst = Join-Path $Deploy $relative
    if (-not (Test-Path $src)) { throw "Required production path missing: $relative" }
    New-Item -ItemType Directory -Path (Split-Path $dst -Parent) -Force | Out-Null
    if ((Get-Item $src).PSIsContainer) { Copy-Item $src $dst -Recurse -Force } else { Copy-Item $src $dst -Force }
}

# Explicit production allowlist. Do not change this to "copy everything".
@(
    "admin", "ajax", "app", "assets", "payment", "views",
    "index.php", "robots.php", "sitemap.php", "img.php", ".htaccess",
    "favicon.ico", "favicon-16x16.png", "favicon-32x32.png", "favicon-48x48.png",
    "android-chrome-192x192.png", "android-chrome-512x512.png", "apple-touch-icon.png", "site.webmanifest",
    "config\.htaccess", "config\config.example.php"
) | ForEach-Object { Copy-Path $_ }

# Runtime upload directories are never copied. Only preserve empty structure files.
foreach ($path in @("uploads\.htaccess", "uploads\products\.gitkeep", "uploads\branding\.gitkeep")) {
    if (Test-Path (Join-Path $Root $path)) { Copy-Path $path }
}

# Never include secrets, database files, docs, development skills, Git metadata, or runtime uploads.
$Zip = Join-Path $Dist ("AB-Socks-v{0}-deploy.zip" -f $Version)
$seven = @("$env:ProgramFiles\7-Zip\7z.exe", "$env:ProgramFiles(x86)\7-Zip\7z.exe") | Where-Object { Test-Path $_ } | Select-Object -First 1
if ($seven) {
    & $seven a -tzip -mx=5 $Zip (Join-Path $Deploy "*") | Out-Null
} elseif (Get-Command tar.exe -ErrorAction SilentlyContinue) {
    Push-Location $Deploy
    try { & tar.exe -a -c -f $Zip * | Out-Null } finally { Pop-Location }
} else {
    # Last resort. Modern Windows PowerShell/Windows 10+ supports this, but 7-Zip/tar is preferred.
    Compress-Archive -Path (Join-Path $Deploy "*") -DestinationPath $Zip -CompressionLevel Optimal
}

# Copy only the release migration as a separate, non-web artifact.
$migrations = Get-ChildItem (Join-Path $Root "database\migrations") -Filter "*_v$Version*.sql" -File
if ($migrations.Count -eq 1) {
    Copy-Item $migrations[0].FullName (Join-Path $Dist $migrations[0].Name)
    Write-Host "Database migration: $($migrations[0].Name)"
} elseif ($migrations.Count -gt 1) {
    throw "More than one migration matched version $Version."
} else {
    Write-Host "Database migration: None required for v$Version"
}

Write-Host "Deployment package created: $Zip"
Write-Host "Deployment directory: $Deploy"
