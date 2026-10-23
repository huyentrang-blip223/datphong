$projectRoot = Split-Path -Parent $PSScriptRoot
$envFile = Join-Path $projectRoot '.env.docker'
docker compose --env-file $envFile ps
docker compose --env-file $envFile exec app php artisan route:list --path=healthz
try {
    Invoke-WebRequest -UseBasicParsing http://127.0.0.1:8080/healthz
} catch {
    Write-Error $_
    exit 1
}
