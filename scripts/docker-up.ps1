$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$envFile = Join-Path $projectRoot '.env.docker'
if (-not (Test-Path -LiteralPath $envFile)) {
    throw 'Missing .env.docker. Copy .env.docker.example to .env.docker and replace every placeholder first.'
}

docker compose --env-file $envFile up -d --build
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
docker compose --env-file $envFile ps
