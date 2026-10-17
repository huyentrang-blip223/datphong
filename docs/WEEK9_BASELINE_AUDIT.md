# Week 9 Baseline Audit

## Baseline
- Local branch: `main`.
- Local baseline before Week 9 work: `95cfceb` (`Implement Week 8 review features`).
- Remote `origin/main`: `682aed4`.
- Week 8 code exists locally but is not pushed to `origin/main`.

## Week 8 status
Implemented locally:
- two themes and theme persistence;
- host availability and seasonal pricing;
- seller product CRUD;
- product order flow;
- mock payment;
- booking history/cancel;
- reviews;
- Week 8 tests and docs.

Week 8 carry-over completed in Week 9:
- notifications UI and service;
- loyalty transaction UI and service;
- audit logging service and writes in key flows;
- host related booking page.

## Tests at baseline / Round 2
- M1: passed 6 tests.
- M2: passed 9 tests.
- M3: passed 6 tests.
- Week8: passed 13 tests.
- Week9: passed 7 tests.
- Full Laravel suite: passed 43 tests.
- Python: passed 5 tests, 2 warnings.

## Deployment files
Prepared:
- `docker-compose.yml`;
- `docker/php/Dockerfile`;
- `docker/python/Dockerfile`;
- `docker/nginx/default.conf`;
- `backend/.env.production.example`;
- `python-service/.env.production.example`;
- `scripts/docker-up.ps1`;
- `scripts/docker-down.ps1`;
- `scripts/docker-doctor.ps1`;
- `scripts/backup-db.ps1`;
- `scripts/restore-db-test.ps1`.

Not executed:
- Docker is not available in PATH.
- `mysql` and `mysqldump` are not available in PATH.

## Critical/High blockers
- `composer audit` found High security advisories affecting Laravel/Symfony dependencies. Laravel major upgrade is explicitly out of scope for this task, so code freeze cannot be marked READY.
- No open Critical/High application bug found in custom code tests.

## Secret/config issues
- `.env` files are ignored.
- `git grep -n "m3-local-token"` returned no matches after cleanup.
- `DB_PASSWORD=` and `PY_SERVICE_TOKEN=` matches are placeholders in env examples only.
- No committed `.env` path was found in Git history scan.
