# PowerShell script to verify session management across the activhub project
# Run this script from the root directory of the activhub project

Write-Host "=== ACTIVHUB SESSION MANAGEMENT VERIFICATION ===" -ForegroundColor Cyan
Write-Host ""

# Check if session_check.php exists
$sessionCheckPath = "includes\session_check.php"
if (Test-Path $sessionCheckPath) {
    Write-Host "Centralized session check file exists: $sessionCheckPath" -ForegroundColor Green
} else {
    Write-Host "Missing centralized session check file: $sessionCheckPath" -ForegroundColor Red
}

# Check auth/login.php for proper session configuration
$loginPath = "auth\login.php"
if (Test-Path $loginPath) {
    $loginContent = Get-Content $loginPath -Raw
    if ($loginContent -match "session_set_cookie_params\(0\)") {
        Write-Host "Login session configuration is correct (prevents random expiration)" -ForegroundColor Green
    } elseif ($loginContent -match "session_set_cookie_params\(1800\)") {
        Write-Host "Login session configuration needs fixing (causes random expiration)" -ForegroundColor Red
        Write-Host "  -> Change session_set_cookie_params(1800) to session_set_cookie_params(0)" -ForegroundColor Yellow
    } else {
        Write-Host "Login session configuration not found or unclear" -ForegroundColor Yellow
    }
} else {
    Write-Host "Login file not found: $loginPath" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== PROTECTED FILES ANALYSIS ===" -ForegroundColor Cyan

# Define directories to scan
$directories = @("admin", "student", "teacher", "cocurricular", "events", "forms")
$totalFiles = 0
$filesWithSessionCheck = 0
$filesWithBasicSession = 0
$filesNeedingUpdate = @()

foreach ($dir in $directories) {
    if (Test-Path $dir) {
        Write-Host ""
        Write-Host "Scanning directory: $dir" -ForegroundColor White
        
        $phpFiles = Get-ChildItem -Path $dir -Filter "*.php" -Recurse
        
        foreach ($file in $phpFiles) {
            $content = Get-Content $file.FullName -Raw
            $totalFiles++
            
            if ($content -match "session_check\.php") {
                Write-Host "  Uses centralized session check: $($file.Name)" -ForegroundColor Green
                $filesWithSessionCheck++
            } elseif ($content -match "session_start\(\)") {
                Write-Host "  Uses basic session_start: $($file.Name)" -ForegroundColor Yellow
                $filesWithBasicSession++
                $filesNeedingUpdate += $file.FullName
            } else {
                Write-Host "  No session management: $($file.Name)" -ForegroundColor Gray
            }
        }
    }
}

Write-Host ""
Write-Host "=== SUMMARY ===" -ForegroundColor Cyan
Write-Host "Total PHP files scanned: $totalFiles"
Write-Host "Files using centralized session check: $filesWithSessionCheck" -ForegroundColor Green
Write-Host "Files using basic session_start: $filesWithBasicSession" -ForegroundColor Yellow
Write-Host "Files needing no session management: $($totalFiles - $filesWithSessionCheck - $filesWithBasicSession)" -ForegroundColor Gray

if ($filesNeedingUpdate.Count -gt 0) {
    Write-Host ""
    Write-Host "FILES THAT COULD BENEFIT FROM CENTRALIZED SESSION CHECK:" -ForegroundColor Yellow
    foreach ($file in $filesNeedingUpdate) {
        Write-Host "  - $file"
    }
    Write-Host ""
    Write-Host "To update these files, run: .\update_session_checks.ps1" -ForegroundColor Cyan
    Write-Host "To preview changes, run: .\update_session_checks.ps1 -DryRun" -ForegroundColor Cyan
}

Write-Host ""
Write-Host "=== RECOMMENDATIONS ===" -ForegroundColor Cyan
Write-Host "1. Ensure auth/login.php uses session_set_cookie_params(0)" -ForegroundColor White
Write-Host "2. Include centralized session check in all protected pages" -ForegroundColor White
Write-Host "3. Test session timeout behavior" -ForegroundColor White
Write-Host "4. Verify logout functionality works from all user types" -ForegroundColor White
