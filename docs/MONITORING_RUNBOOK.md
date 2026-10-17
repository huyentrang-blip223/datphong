# Monitoring Runbook Week 9

## Health checks
- Laravel: `GET /healthz`
- Python: `GET /healthz` on the Python service
- Docker: `scripts/docker-doctor.ps1`

## Logs
- Laravel logs: `backend/storage/logs`
- Python runtime logs: container stdout or local terminal
- Nginx logs: container logs

Do not paste secrets or tokens into issue reports.

## Restart
```powershell
docker compose restart app python nginx
```

## Backup
Use `scripts/backup-db.ps1` with DB environment variables set. Restore validation uses `scripts/restore-db-test.ps1`.

## Rollback
Use the previous Git commit and restore a verified database backup. Do not mark rollback complete until health and login smoke checks pass.
