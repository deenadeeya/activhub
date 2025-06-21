# PowerShell script to add centralized session check to all protected pages
# Run this script from the root directory of the activhub project

param(
    [switch]$DryRun = $false
)

Write-Host "Scanning for PHP files that need session check..."

# Define directories to scan
$directories = @(
    "admin",
    "student", 
    "teacher",
    "cocurricular",
    "events",
    "forms"
)

# Files that already have proper session handling or are public
$excludeFiles = @(
    "login.php",
    "logout.php",
    "connect.php"
)

$filesToUpdate = @()

foreach ($dir in $directories) {
    if (Test-Path $dir) {
        $phpFiles = Get-ChildItem -Path $dir -Filter "*.php" -Recurse
        
        foreach ($file in $phpFiles) {
            $fileName = $file.Name
            
            # Skip excluded files
            if ($excludeFiles -contains $fileName) {
                continue
            }
            
            # Read file content
            $content = Get-Content $file.FullName -Raw
            
            # Check if file doesn't already include session_check.php
            if ($content -notmatch "session_check\.php" -and $content -match "session_start\(\)") {
                $filesToUpdate += $file.FullName
                Write-Host "Found file needing update: $($file.FullName)"
            }
        }
    }
}

if ($filesToUpdate.Count -eq 0) {
    Write-Host "No files need updating." -ForegroundColor Green
    exit
}

Write-Host "`nFound $($filesToUpdate.Count) files that need session check updates."

if ($DryRun) {
    Write-Host "DRY RUN MODE - No files will be modified" -ForegroundColor Yellow
    foreach ($file in $filesToUpdate) {
        Write-Host "Would update: $file"
    }
    exit
}

Write-Host "Proceeding with updates..." -ForegroundColor Green

foreach ($filePath in $filesToUpdate) {
    try {
        $content = Get-Content $filePath -Raw
        
        # Determine the correct relative path to includes/session_check.php
        $relativePath = "../includes/session_check.php"
        $fileDir = Split-Path $filePath -Parent
        $baseName = Split-Path $fileDir -Leaf
        
        # Adjust path based on directory depth
        switch ($baseName) {
            "function" { $relativePath = "../../includes/session_check.php" }
            default { $relativePath = "../includes/session_check.php" }
        }
        
        # Replace session_start() with include session_check.php
        if ($content -match "session_start\(\);") {
            $newContent = $content -replace "session_start\(\);", "require_once '$relativePath';"
            Set-Content -Path $filePath -Value $newContent -NoNewline
            Write-Host "Updated: $filePath" -ForegroundColor Green
        }
    }
    catch {
        Write-Host "Error updating $filePath : $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "`nSession check updates completed!" -ForegroundColor Green
Write-Host "Remember to test all protected pages to ensure proper functionality."
