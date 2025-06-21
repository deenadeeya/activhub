# PowerShell script to update file paths after reorganization

# Function to update paths in files
function Update-Paths {
    param(
        [string]$Directory,
        [hashtable]$Replacements
    )
    
    Get-ChildItem -Path $Directory -Filter "*.php" -Recurse | ForEach-Object {
        $content = Get-Content $_.FullName -Raw
        $updated = $false
        
        foreach ($old in $Replacements.Keys) {
            if ($content -match [regex]::Escape($old)) {
                $content = $content -replace [regex]::Escape($old), $Replacements[$old]
                $updated = $true
            }
        }
        
        if ($updated) {
            Set-Content -Path $_.FullName -Value $content -NoNewline
            Write-Host "Updated: $($_.FullName)"
        }
    }
}

# Update paths for files in student/ directory
$studentReplacements = @{
    "require_once 'connect.php'" = "require_once '../config/connect.php'"
    "include 'connect.php'" = "include '../config/connect.php'"
    "include 'header.php'" = "include '../includes/header.php'"
    "include 'navbar.php'" = "include '../includes/navbar.php'"
    "include 'navlinks.php'" = "include '../includes/navlinks.php'"
    'href="css/' = 'href="../assets/css/'
    'href="../css/' = 'href="../assets/css/'
    'src="img/' = 'src="../assets/img/'
    'src="../img/' = 'src="../assets/img/'
    'href="/img/' = 'href="../assets/img/'
}

# Update paths for files in cocurricular/ directory
$cocurricularReplacements = @{
    "require_once 'connect.php'" = "require_once '../config/connect.php'"
    "include 'connect.php'" = "include '../config/connect.php'"
    "include 'header.php'" = "include '../includes/header.php'"
    "include 'navbar.php'" = "include '../includes/navbar.php'"
    "include 'navlinks.php'" = "include '../includes/navlinks.php'"
    'href="css/' = 'href="../assets/css/'
    'src="img/' = 'src="../assets/img/'
    'href="/img/' = 'href="../assets/img/'
    'src="logos/' = 'src="../assets/logos/'
    'href="logos/' = 'href="../assets/logos/'
}

# Update paths for files in events/ directory
$eventsReplacements = @{
    "require_once 'connect.php'" = "require_once '../config/connect.php'"
    "include 'connect.php'" = "include '../config/connect.php'"
    "include 'header.php'" = "include '../includes/header.php'"
    "include 'navbar.php'" = "include '../includes/navbar.php'"
    "include 'navlinks.php'" = "include '../includes/navlinks.php'"
    'href="css/' = 'href="../assets/css/'
    'src="img/' = 'src="../assets/img/'
    'href="/img/' = 'href="../assets/img/'
}

# Update paths for files in forms/ directory
$formsReplacements = @{
    "require_once 'connect.php'" = "require_once '../config/connect.php'"
    "include 'connect.php'" = "include '../config/connect.php'"
    "include 'header.php'" = "include '../includes/header.php'"
    "include 'navbar.php'" = "include '../includes/navbar.php'"
    "include 'navlinks.php'" = "include '../includes/navlinks.php'"
    'href="css/' = 'href="../assets/css/'
    'src="img/' = 'src="../assets/img/'
    'href="/img/' = 'href="../assets/img/'
}

Write-Host "Updating student files..."
Update-Paths -Directory "student" -Replacements $studentReplacements

Write-Host "Updating cocurricular files..."
Update-Paths -Directory "cocurricular" -Replacements $cocurricularReplacements

Write-Host "Updating events files..."
Update-Paths -Directory "events" -Replacements $eventsReplacements

Write-Host "Updating forms files..."
Update-Paths -Directory "forms" -Replacements $formsReplacements

Write-Host "Path updates completed!"
