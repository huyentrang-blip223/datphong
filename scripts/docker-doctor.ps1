docker compose ps
docker compose exec app php artisan route:list --path=healthz
try {
    Invoke-WebRequest -UseBasicParsing http://127.0.0.1:8080/healthz
} catch {
    Write-Error $_
    exit 1
}
