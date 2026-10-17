param(
    [string]$OutputDir = "backups",
    [string]$MysqlDump = "mysqldump"
)

$db = $env:DB_DATABASE
$user = $env:DB_USERNAME
$pass = $env:DB_PASSWORD
$hostName = $env:DB_HOST
$port = if ($env:DB_PORT) { $env:DB_PORT } else { "3306" }

if (-not $db -or -not $user -or -not $hostName) {
    Write-Error "DB_DATABASE, DB_USERNAME and DB_HOST environment variables are required."
    exit 1
}

New-Item -ItemType Directory -Force -Path $OutputDir | Out-Null
$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$out = Join-Path $OutputDir "$db-$timestamp.sql"

$env:MYSQL_PWD = $pass
& $MysqlDump "--host=$hostName" "--port=$port" "--user=$user" --single-transaction --routines --triggers $db | Out-File -Encoding utf8 $out
$env:MYSQL_PWD = $null

Write-Output "BACKUP_CREATED=$out"
