# CODE_STATUS.md — Handoff from Codex to ChatGPT

Updated at the end of this coding session: 2026-10-02.

## Current milestone
Week 7 / M3.

## What was implemented
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

## Files changed
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

## Tests actually executed
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
- `backend/.env` contains local runtime `PY_SERVICE_TOKEN=m3-local-token`; `.env` is untracked and should not be committed.
- FastAPI was started as a background process for verification; stop it manually if no longer needed.
- No screenshots were captured by Codex.
- Current M3 changes are not committed or pushed at this handoff.

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

## Git evidence
- M3 changes are not committed in this handoff.
- Previous local M2 data seeder commit existed as `5995ca3 Add M2 demo tourism dataset seeder`.
- Run `git status --short` and commit the M3 changes only after reviewing local `.env` is not staged.
