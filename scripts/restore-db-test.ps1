param(
    [Parameter(Mandatory=$true)][string]$BackupFile,
    [string]$Mysql = "mysql"
)

$user = $env:DB_USERNAME
$pass = $env:DB_PASSWORD
$hostName = $env:DB_HOST
$port = if ($env:DB_PORT) { $env:DB_PORT } else { "3306" }
$testDb = "dt07_restore_test_" + (Get-Date -Format "yyyyMMddHHmmss")

if (-not (Test-Path $BackupFile)) {
    Write-Error "Backup file not found."
    exit 1
}
if (-not $user -or -not $hostName) {
    Write-Error "DB_USERNAME and DB_HOST environment variables are required."
    exit 1
}

$env:MYSQL_PWD = $pass
& $Mysql "--host=$hostName" "--port=$port" "--user=$user" -e "CREATE DATABASE $testDb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
Get-Content $BackupFile | & $Mysql "--host=$hostName" "--port=$port" "--user=$user" $testDb
& $Mysql "--host=$hostName" "--port=$port" "--user=$user" -e "SELECT 'homestays' table_name, COUNT(*) rows_count FROM $testDb.homestays UNION ALL SELECT 'rooms', COUNT(*) FROM $testDb.rooms UNION ALL SELECT 'bookings', COUNT(*) FROM $testDb.bookings UNION ALL SELECT 'local_products', COUNT(*) FROM $testDb.local_products;"
& $Mysql "--host=$hostName" "--port=$port" "--user=$user" -e "DROP DATABASE $testDb;"
$env:MYSQL_PWD = $null

Write-Output "RESTORE_TEST_DB_DROPPED=$testDb"
