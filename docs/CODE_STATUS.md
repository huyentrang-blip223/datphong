# CODE_STATUS.md — Handoff from Codex to ChatGPT

## Latest delivery — accounts/cart/Render, 2026-10-18

Final verification: **91 Laravel tests passed**, **6 Python tests passed (2 deprecation warnings)**, **153 PHP files passed syntax**, **30 Chrome UI checks passed at three widths**. Account recovery/profile/admin management, cart/redemption, VNPay sandbox refund/query/IPN and operational scripts were added. Windows scheduler ran with exit 0; MySQL backup and isolated restore matched 29 tables/views. New deployment initialization (30 objects/two triggers), demo future inventory (3,240 rows) and repeat startup were verified on disposable databases.

Render assets are prepared, not deployed. Account connection and an external MySQL connection are still required. The existing VNPay merchant probe returned **Website này chưa được phê duyệt**; external payment/refund success is not claimed. Docker Desktop could not start. See `ACCOUNTS_CART_OPERATIONS_2026-10-18.md` and `RENDER_DEPLOYMENT.md`. Older status sections below are historical.

## Current update — 2026-10-18

Missing booking, verification, experience-management, search-pagination and reporting flows were completed. Current evidence: **73 Laravel tests passed**, **5 Python tests passed (2 deprecation warnings)**, **123 PHP files passed syntax checks**, and **18 Chrome UI checks passed across six pages at 390/768/1440 px**. The expiry command is registered every minute in `schedule:list`.

See `COMPLETED_FLOWS_2026-10-18.md` for the exact changed-file inventory, commands and manual checks, and `PROJECT_CONTEXT.md` section 16 for business rules. Refunds are explicitly academic ledger simulations. Hosting, real VNPay sandbox merchant verification, Docker runtime, three-engine testing and usability evidence are not claimed complete.

The following sections are historical snapshots; statements that seller CRUD, notifications or loyalty are missing have been superseded.

Updated at the end of this coding session: 2026-10-02.

## Current milestone
Week 9 / Code Freeze & Deployment.

Baseline: `682aed4` — `Implement M3 Python recommendations and analytics`.

Current local baseline before Week 9 work: `95cfceb` — `Implement Week 8 review features`.

## Code freeze
Status: NOT READY.

Reason: application regression tests pass, but `composer audit` found High dependency advisories and Docker/backup/restore/browser/performance checks could not be executed in this environment.

## What was implemented before Week 8
- Kept the running backend on Laravel 9; no framework upgrade.
- Preserved M1/M2 booking flow and added a scoped logout stale-token fix so POST `/dang-xuat` does not show 419 Page Expired for expired CSRF tokens.
- Added local product browsing/filter/detail pages.
- Added Python-backed product recommendations on product detail pages.
- Added Laravel fallback recommendations when Python is down/timeout/error.
- Added cultural experience listing/detail pages.
- Added guest experience booking with transaction, row lock, capacity re-check, and HTTP 409 conflict handling.
- Added `PythonDataService` with timeout, retry, cache, token header, logging, and fallback.
- Added FastAPI Python service for recommendations, analytics, cache refresh, and health checks.
- Added ETL cleaning script with dry-run, output CSV, stats JSON, logging, and descriptive stats.
- Added admin dashboard analytics blocks for seasonality and top homestays.
- Added Chapter 1 and Chapter 2 technical draft Markdown files grounded in actual runtime/code.
- Confirmed runtime data count is >=300.

## Week 8 features completed
- Host availability/calendar management with range update, ownership checks, `units_total >= units_held + units_sold` guard and tests.
- Host seasonal pricing create/delete with date validation and ownership checks.
- Host cultural experience CRUD with ownership checks and deletion guard for experiences that already have bookings.
- Seller local product CRUD with validation and seller-only ownership.
- Guest product order creation with DB-computed totals, stock check, transaction and item persistence.
- Seller order state transitions with invalid transition blocking.
- Mock/test payment for bookings with success/fail and duplicate-paid guard; no card data is stored.
- Guest booking history and cancel with status rule and availability rollback.
- Guest review creation limited to completed own bookings and rating validation.
- `/healthz` endpoint for application/database/Python degraded status without secrets.
- Two themes: `light` and `forest`, implemented with CSS custom properties, `data-theme`, theme toggle, `localStorage` persistence and `prefers-color-scheme` fallback.
- Responsive CSS for 390/768/1440 targets: wrapped nav, form grids, focus state, responsive images and horizontally scrollable tables.
- Shared academic footer noting course, topic, project-seed data and Week 8 version.

## Week 9 features completed
- Notifications model/service/pages and mark-read route.
- Loyalty transaction model/service/page and point awards on mock paid booking and delivered product orders.
- AuditLog model/service and audit writes for booking, payment, order transition and review.
- Host related-bookings page scoped by homestay ownership.
- Login rate limiting with `throttle:login`; Week9 test confirms 429 after repeated failures.
- Public guest-only registration with password hashing and immediate login; host/seller/admin roles are not self-selectable.
- Homestay cover image upload for admin create/update, with JPG/PNG/WebP validation and public disk storage.
- Security headers middleware: `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, CSP, and conditional HSTS on HTTPS.
- Friendly Blade error pages for 403/404/419/500.
- `/healthz` returns application/database/python/timestamp and does not expose secrets or exception details.
- Deployment package: Docker Compose, Nginx config, PHP/Python Dockerfiles, production env examples, backup/restore scripts, README, deployment/runbook/testing docs.

## Files changed before Week 8
- `backend/.env` local only: added `PY_SERVICE_URL`, `PY_SERVICE_TOKEN`, `PY_SERVICE_TIMEOUT` for runtime verification; this file is not tracked by Git.
- `backend/.env.example`
- `backend/app/Exceptions/Handler.php`
- `backend/app/Http/Controllers/DashboardController.php`
- `backend/app/Http/Controllers/ExperienceController.php`
- `backend/app/Http/Controllers/Product/LocalProductController.php`
- `backend/app/Http/Requests/StoreExperienceBookingRequest.php`
- `backend/app/Models/Experience.php`
- `backend/app/Models/ExperienceBooking.php`
- `backend/app/Models/Homestay.php`
- `backend/app/Models/LocalProduct.php`
- `backend/app/Services/ExperienceBookingService.php`
- `backend/app/Services/PythonDataService.php`
- `backend/config/services.php`
- `backend/resources/views/dashboard/admin.blade.php`
- `backend/resources/views/experiences/index.blade.php`
- `backend/resources/views/experiences/show.blade.php`
- `backend/resources/views/layouts/app.blade.php`
- `backend/resources/views/products/index.blade.php`
- `backend/resources/views/products/show.blade.php`
- `backend/routes/web.php`
- `backend/tests/Feature/M1AuthRoleSmokeTest.php`
- `backend/tests/Feature/M3PythonIntegrationTest.php`
- `docs/CHUONG_1_M3_NHAP.md`
- `docs/CHUONG_2_M3_NHAP.md`
- `docs/PROJECT_CONTEXT.md`
- `docs/CODE_STATUS.md`
- `python-service/.env.example`
- `python-service/README.md`
- `python-service/app/__init__.py`
- `python-service/app/analytics.py`
- `python-service/app/db.py`
- `python-service/app/etl_clean.py`
- `python-service/app/main.py`
- `python-service/app/recommender.py`
- `python-service/data/raw/local_products_seed.csv`
- `python-service/data/clean/local_products_clean.csv`
- `python-service/data/clean/local_products_clean.stats.json`
- `python-service/requirements.txt`
- `python-service/tests/test_api.py`
- `python-service/tests/test_etl.py`

## Week 8 files changed/added
- `backend/routes/web.php`
- `backend/public/css/app.css`
- `backend/public/js/theme.js`
- `backend/resources/views/layouts/app.blade.php`
- `backend/resources/views/host/availability/index.blade.php`
- `backend/resources/views/host/seasonal_prices/index.blade.php`
- `backend/resources/views/seller/products/*`
- `backend/resources/views/orders/index.blade.php`
- `backend/resources/views/bookings/index.blade.php`
- `backend/resources/views/products/show.blade.php`
- `backend/resources/views/host/rooms/index.blade.php`
- `backend/resources/views/dashboard/host.blade.php`
- Week 8 controllers, services, requests and models for availability, seasonal prices, seller products, product orders, payments, reviews and health.
- `backend/tests/Feature/Week8AvailabilityPricingTest.php`
- `backend/tests/Feature/Week8OrderPaymentReviewTest.php`
- Week 8 docs under `docs/` and `docs/diagrams/`.
- `e2e/README.md`

## Week 9 files changed/added
- `backend/app/Models/Notification.php`
- `backend/app/Models/LoyaltyTransaction.php`
- `backend/app/Models/AuditLog.php`
- `backend/app/Services/NotificationService.php`
- `backend/app/Services/LoyaltyService.php`
- `backend/app/Services/AuditLogService.php`
- `backend/app/Http/Controllers/NotificationController.php`
- `backend/app/Http/Controllers/LoyaltyController.php`
- `backend/app/Http/Controllers/Host/BookingController.php`
- `backend/app/Http/Middleware/SecurityHeaders.php`
- `backend/tests/Feature/Week9FreezeReadinessTest.php`
- deployment files under `docker/`, `scripts/`, production env examples, and Week9 docs.

## Database/schema changes
- No migrations were added or run.
- M3 reuses existing Week 4 tables.
- Runtime data was loaded earlier through `M2TourismDatasetSeeder`; no destructive reset was performed.

## Data load evidence
Configured DB connection: `dt07_homestay`.

| Table | Count |
|---|---:|
| homestays | 15 |
| rooms | 36 |
| room_availability | 360 |
| seasonal_prices | 36 |
| bookings | 24 |
| local_products | 24 |
| homestay_products | 24 |
| product_orders | 20 |
| product_order_items | 40 |
| experiences | 24 |
| experience_bookings | 24 |
| reviews | 20 |
| tourism_total main | 583 |

Result: runtime data meets the >=300 M2/M3 requirement. Dataset is deterministic synthetic/project-seed data, not the previously documented 3,850-record target dataset.

## Python environment
- `python --version`: Python 3.13.15
- Installed/observed packages:
  - fastapi 0.142.2
  - uvicorn 0.52.4
  - pandas 2.3.2
  - numpy 2.3.2
  - scikit-learn 1.7.2
  - PyMySQL import-reported version 2.2.8
  - python-dotenv installed
  - pydantic 2.13.5
  - pytest 8.4.2
  - httpx 0.28.1
- SQLAlchemy 2.1.2 installed, but runtime DB access uses PyMySQL directly because Windows Application Control blocked SQLAlchemy's `_immutabledict_cy` DLL during pytest collection.
- FastAPI run command used:

```bash
python -m uvicorn app.main:app --host 127.0.0.1 --port 8001
```

## Python endpoints
- `GET /healthz`
- `GET /recommend/local-products/{product_id}?k=6`
- `GET /analytics/seasonality`
- `GET /analytics/top-homestays?limit=10`
- `POST /cache/refresh`

Protected endpoints require `X-Service-Token`. `/healthz` is public local.

## Laravel routes/pages added
- `GET /san-pham-dia-phuong` -> local product listing/filter.
- `GET /san-pham-dia-phuong/{product}` -> product detail with Python recommendation/fallback.
- `GET /trai-nghiem` -> experience listing/filter.
- `GET /trai-nghiem/{experience}` -> experience detail.
- `POST /trai-nghiem/{experience}/dat-cho` -> guest experience booking.
- Admin dashboard `/quan-tri/bang-dieu-khien` now shows analytics blocks.

## Commands actually run
```bash
Get-Content -Raw AGENTS.md
Get-Content -Raw docs/PROJECT_CONTEXT.md
Get-Content -Raw docs/CODE_STATUS.md
Get-Content -Raw docs/NEXT_TASK_WEEK7_M3_FULL_DT07.md
Get-Content -Raw database/schema_DT07.sql
Get-Content -Raw backend/composer.json
python --version
python -c "import fastapi, pandas, numpy, sklearn, sqlalchemy, pymysql, dotenv, pytest, httpx; print('imports-ok')"
python -m pip install -r python-service/requirements.txt
python -m pytest -q
python -m app.etl_clean --input data/raw/local_products_seed.csv --output data/clean/local_products_clean.csv --dry-run
python -c "...package version print..."
python -c "from app.analytics import seasonality, top_homestays; ..."
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan cache:clear
C:\xampp\php\php.exe artisan test --filter=M1AuthRoleSmokeTest
C:\xampp\php\php.exe artisan test --filter=M2BookingFlowTest
C:\xampp\php\php.exe artisan test --filter=M3PythonIntegrationTest
C:\xampp\php\php.exe artisan test
C:\xampp\php\php.exe -r "...runtime COUNT script..."
Get-ChildItem -Recurse app,tests -Filter *.php | ForEach-Object { C:\xampp\php\php.exe -l $_.FullName }
Start-Process -WindowStyle Hidden -FilePath python -ArgumentList @('-m','uvicorn','app.main:app','--host','127.0.0.1','--port','8001')
Invoke-WebRequest http://127.0.0.1:8001/healthz
Invoke-WebRequest http://127.0.0.1:8001/recommend/local-products/25?k=3
Invoke-WebRequest http://127.0.0.1:8001/analytics/seasonality
Invoke-WebRequest http://127.0.0.1:8001/analytics/top-homestays?limit=3
Invoke-WebRequest http://127.0.0.1:8000/san-pham-dia-phuong/25
Invoke-WebRequest admin login/dashboard check
netstat -ano | Select-String ':8001|:8002|:8003'
Stop-Process -Id 11556,7792,4796 -Force
```

## Tests actually executed before Week 8
| Test/command | Result |
|---|---|
| Initial Python import check | Failed before install: `ModuleNotFoundError: No module named 'fastapi'`. |
| Initial `pip install` without escalation | Failed: sandbox/network `WinError 10013`. |
| Escalated `python -m pip install -r python-service/requirements.txt` | Passed. |
| Initial `pytest` after install | Failed: SQLAlchemy C-extension DLL blocked by Windows Application Control. |
| Final `python -m pytest -q` | Passed: 5 passed, 2 warnings. |
| ETL dry-run | Passed: rows_before=6, rows_after=6, rows_removed=0. |
| PHP syntax check over `backend/app` and `backend/tests` | Passed: no syntax errors. |
| `php artisan test --filter=M1AuthRoleSmokeTest` | Passed: 6 tests, 6 passed. |
| `php artisan test --filter=M2BookingFlowTest` | Passed: 9 tests, 9 passed. |
| `php artisan test --filter=M3PythonIntegrationTest` | Passed: 6 tests, 6 passed. |
| `php artisan test` | Passed: 23 tests, 23 passed. |

## Week 8 tests actually executed on 2026-10-03
| Test/command | Result |
|---|---|
| Baseline `php artisan test --filter=M1AuthRoleSmokeTest` | Passed: 6 tests. |
| Baseline `php artisan test --filter=M2BookingFlowTest` | Passed: 9 tests. |
| Baseline `php artisan test --filter=M3PythonIntegrationTest` | Passed: 6 tests. |
| Baseline `php artisan test` | Passed: 23 tests. |
| Baseline `python -m pytest -q` | Passed: 5 tests, 2 warnings. |
| PHP syntax check over `backend/app` and `backend/tests` | Passed: no syntax errors. |
| `php artisan test --filter=Week8` | Passed: 13 tests. |
| Final `php artisan test` | Passed: 36 tests. |
| `php artisan route:list --path=healthz` | Passed: `GET|HEAD healthz` registered. |

## Week 9 tests/checks actually executed on 2026-10-03
| Test/command | Result |
|---|---|
| PHP syntax check over `backend/app` and `backend/tests` | Passed: no syntax errors after PHP 8.0 compatibility fix. |
| `php artisan test --filter=M1AuthRoleSmokeTest` | Passed: 10 tests. |
| `php artisan test --filter=M2BookingFlowTest` | Passed: 9 tests. |
| `php artisan test --filter=M3PythonIntegrationTest` | Passed: 6 tests. |
| `php artisan test --filter=Week8` | Passed: 15 tests. |
| `php artisan test --filter=Week9` | Passed: 8 tests. |
| `php artisan test` | Passed: 50 tests. |
| `python -m pytest -q` | Passed: 5 tests, 2 warnings. |
| `composer audit` | Failed/blocker: 16 advisories affecting 7 packages, includes High advisories. |
| `python -m pip list --outdated` | Executed; multiple outdated packages listed. |
| `docker --version` / `docker compose version` | NOT EXECUTED/BLOCKED: `docker` unavailable in PATH. |
| `mysql --version` / `mysqldump --version` | NOT EXECUTED/BLOCKED: commands unavailable in PATH. |
| `node --version`, `npm --version`, `npx playwright --version`, `npx lighthouse --version` | BLOCKED: commands unavailable in PATH. |
| Secret scan `git grep -n "m3-local-token"` | Passed after cleanup: no matches. |
| Git history `.env` scan | Passed: no committed `.env` paths returned. |

Round 1 functional matrix:
- Designed: 60.
- PASS: 51.
- FAILED: 0.
- BLOCKED: 0.
- NOT EXECUTED: 9.
- CSV: `docs/TEST_ROUND1_WEEK8.csv`.

Round 2 functional matrix:
- Designed: 60.
- PASS: 52.
- FAILED: 0.
- BLOCKED: 0.
- NOT EXECUTED: 8.
- CSV: `docs/TEST_ROUND2_WEEK9.csv`.

Browser matrix:
- Attempted tool check: `node --version`, `npm --version`, `npx playwright --version`, `npx lighthouse --version`.
- Result: all unavailable in PATH.
- `docs/BROWSER_MATRIX_WEEK8.csv` records browser/viewport cases as BLOCKED.
- Week9 browser retest remains BLOCKED for the same reason; see `docs/BROWSER_MATRIX_WEEK9.csv`.

Lighthouse/performance:
- NOT EXECUTED because `npx`/Lighthouse are unavailable.
- Recorded in `docs/PERFORMANCE_WEEK8.csv`.
- Week9 performance remains NOT EXECUTED; see `docs/PERFORMANCE_WEEK9.csv`.

## Deployment readiness Week 9
- Docker package: PREPARED, NOT EXECUTED because Docker is unavailable.
- Nginx config: PREPARED.
- Production env examples: PREPARED.
- Health endpoint: PASS in Week9 test.
- Backup/restore scripts: PREPARED, NOT EXECUTED because MySQL CLI tools are unavailable.
- Go-live: BLOCKED — hosting/domain credentials not provided.

## Integration evidence
- FastAPI up: `GET http://127.0.0.1:8001/healthz` returned `{"status":"ok"}`.
- Recommendation endpoint: `GET /recommend/local-products/25?k=3` with token returned 3 product recommendations and did not return product 25.
- Analytics endpoint: `GET /analytics/seasonality` returned month `2026-10`, `units_total=960`, `units_sold=24`, `occupancy_rate=0.025`.
- Laravel recommendation page: `GET http://127.0.0.1:8000/san-pham-dia-phuong/25` returned HTTP 200 and contained `Python service`.
- Python down fallback: after setting `PY_SERVICE_URL=http://127.0.0.1:8999` and clearing cache/config, the same product page returned HTTP 200 and contained `Laravel fallback`.
- Admin analytics: after admin login, `/quan-tri/bang-dieu-khien` returned HTTP 200 and contained `Python service`, `2026-10`, and `Top homestay`.

## Known issues
- The full 3,850-record target dataset is not present in this repo snapshot; runtime proof uses the 583-record deterministic demo dataset.
- Seller CRUD for local products was not added. Schema supports seller ownership, but M3 prioritized browsing, recommendation, experience booking, ETL and dashboard analytics.
- SQLAlchemy is installed but not used at runtime because importing its C-extension was blocked by local Windows Application Control.
- `backend/.env` contains a local runtime `PY_SERVICE_TOKEN`; `.env` is untracked and should not be committed.
- FastAPI was started as a background process for verification; stop it manually if no longer needed.
- No screenshots were captured by Codex.
- Week 8 browser matrix could not run because Node/npm/npx are unavailable in PATH.
- Week 8 Lighthouse could not run because npx/Lighthouse are unavailable.
- Diagram sources were created but not rendered to PNG/SVG.
- 9 of 60 functional round 1 cases are NOT EXECUTED and documented as such.
- Notifications and loyalty UI remain outside this Week 8 implementation.
- Composer audit High advisories remain open because Laravel major upgrade is disallowed by the task.
- Docker build/up was not executed because Docker is unavailable in PATH.
- Backup/restore was not executed because `mysql` and `mysqldump` are unavailable in PATH.
- Browser and Lighthouse checks remain blocked because Node/npm/npx are unavailable.
- `README_M1.md` is locally modified outside this Week9 work and was left untouched.

## Screenshot list
- FastAPI `/healthz` returning `{"status":"ok"}`.
- FastAPI recommendation JSON for `/recommend/local-products/25?k=3`.
- Laravel product detail `/san-pham-dia-phuong/25` showing recommendation source `Python service`.
- Laravel product detail after Python down/unavailable showing `Laravel fallback`.
- Admin dashboard `/quan-tri/bang-dieu-khien` showing analytics source `Python service`.
- Terminal `python -m pytest -q`.
- Terminal `php artisan test --filter=M3PythonIntegrationTest`.
- Terminal full `php artisan test`.
- ETL dry-run output showing rows before/after and stats.
- Database COUNT query showing `tourism_total main = 583`.
- Git status/diff/commit evidence after user chooses to commit.

## Screenshot list Week 8
- Light theme desktop.
- Forest theme desktop.
- Mobile 390 search.
- Tablet 768 product/experience.
- Desktop 1440 admin dashboard.
- Theme switch after reload.
- Host availability/calendar.
- Host seasonal pricing.
- Seller product CRUD.
- Product order/payment test mode.
- Review valid and blocked.
- Week8 test output.
- Bug log round 1.
- `/healthz`.

## Screenshot list Week 9
- `/healthz` JSON with application/database/python/timestamp.
- Security headers from browser/devtools or curl equivalent.
- Guest notifications page.
- Guest loyalty points page.
- Host related bookings page.
- Docker files in repo/package.
- Full Laravel test output showing 50 passed.
- Python pytest output showing 5 passed.
- Composer audit blocker output.
- Code freeze checklist showing NOT READY blockers.

## Git evidence
- Baseline before Week 8: `682aed4`.
- Week 8 local commit: `95cfceb`.
- Week 9 changes are not committed unless the user explicitly requests commit/push.
