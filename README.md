# ĐT-07 Homestay Cộng Đồng

Laravel 9 + MySQL + Blade/CSS/JS + FastAPI/Python project for a community homestay marketplace linked with local products and cultural experiences.

## Runtime
- Backend: Laravel 9.
- Database: MySQL 8 compatible schema in `database/schema_DT07.sql`.
- Frontend: Blade with shared CSS/JS, light and forest themes.
- Python: FastAPI service in `python-service` for product recommendations and analytics.

## Local checks
```powershell
cd backend
C:\xampp\php\php.exe artisan test

cd ..\python-service
python -m pytest -q
```

## Health
Laravel exposes `GET /healthz` with application, database, Python status and timestamp. Python exposes `GET /healthz`.

## Docker package
The Compose package builds immutable Laravel, Nginx and FastAPI images, runs MySQL 8, initializes an empty schema without overwriting an existing database, and runs the Laravel scheduler.

Local/VPS startup:

```powershell
Copy-Item .env.docker.example .env.docker
# Generate APP_KEY with: C:\xampp\php\php.exe backend\artisan key:generate --show
# Edit .env.docker: set APP_KEY, database passwords, admin credentials and Python token.
.\scripts\docker-up.ps1
.\scripts\docker-doctor.ps1
```

Open `http://127.0.0.1:8080`. To stop without deleting MySQL or uploaded-file volumes:

```powershell
.\scripts\docker-down.ps1
```

For a public Linux VPS with DNS pointing to the server, set `APP_URL=https://your-domain`, `SESSION_SECURE_COOKIE=true`, `TRUSTED_PROXIES=*`, `DT07_DOMAIN` and `ACME_EMAIL` in `.env.docker`, then start Caddy HTTPS:

```bash
docker compose --env-file .env.docker -f docker-compose.yml -f docker-compose.https.yml up -d --build
```

Do not run `docker compose down -v` unless you intentionally want to delete the database and uploaded files. Render deployment uses the separate `render.yaml`; see `docs/RENDER_DEPLOYMENT.md`.

Do not commit real `.env`, passwords, tokens, SQL dumps, or logs.

## Demo data
Current runtime evidence uses deterministic synthetic/project-seed data, not real personal data.

## Known deployment limitation
No public hosting/domain credentials are provided in this repository. Go-live must be treated as blocked until real hosting and secret configuration are supplied.
