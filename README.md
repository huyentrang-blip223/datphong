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
Deployment package is prepared with `docker-compose.yml`, `docker/nginx/default.conf`, `docker/php/Dockerfile`, and `docker/python/Dockerfile`.

Use production env placeholders from:
- `backend/.env.production.example`
- `python-service/.env.production.example`

Do not commit real `.env`, passwords, tokens, SQL dumps, or logs.

## Demo data
Current runtime evidence uses deterministic synthetic/project-seed data, not real personal data.

## Known deployment limitation
No public hosting/domain credentials are provided in this repository. Go-live must be treated as blocked until real hosting and secret configuration are supplied.
