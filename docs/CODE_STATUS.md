# CODE_STATUS.md — Handoff from Codex to ChatGPT

Updated at the end of this coding session: 2026-10-02.

## Current milestone
Week 6 / M2: first tourism business flow implemented on the existing Laravel 9 app without framework upgrade.

## What was implemented
- Added room-management model/controller/request/policy flow for host-owned rooms.
- Added public homestay/room search by location, check-in, check-out, guest count, and optional price range.
- Added `BookingService` for booking business rules instead of placing booking logic in controllers.
- Implemented quote calculation with checkout-exclusive nights and price precedence:
  `price_override` -> matching `seasonal_prices` row -> `rooms.base_price`.
- Implemented booking creation inside a DB transaction with deterministic `room_availability.stay_date` ordering and `lockForUpdate()`.
- Re-checks availability after locking and returns HTTP 409 for JSON availability conflicts.
- Creates `bookings`, increments `room_availability.units_sold`, and writes `booking_status_logs`.
- Added focused M2 feature tests plus M1 regression coverage through the full suite.

## Files changed
- `backend/app/Exceptions/AvailabilityConflictException.php`
- `backend/app/Http/Controllers/BookingController.php`
- `backend/app/Http/Controllers/Host/RoomController.php`
- `backend/app/Http/Controllers/SearchController.php`
- `backend/app/Http/Requests/SearchRoomsRequest.php`
- `backend/app/Http/Requests/StoreBookingRequest.php`
- `backend/app/Http/Requests/StoreRoomRequest.php`
- `backend/app/Http/Requests/UpdateRoomRequest.php`
- `backend/app/Models/Booking.php`
- `backend/app/Models/BookingStatusLog.php`
- `backend/app/Models/Homestay.php`
- `backend/app/Models/Room.php`
- `backend/app/Models/RoomAvailability.php`
- `backend/app/Models/SeasonalPrice.php`
- `backend/app/Models/User.php`
- `backend/app/Policies/RoomPolicy.php`
- `backend/app/Providers/AuthServiceProvider.php`
- `backend/app/Services/BookingService.php`
- `backend/resources/views/host/rooms/_form.blade.php`
- `backend/resources/views/host/rooms/create.blade.php`
- `backend/resources/views/host/rooms/edit.blade.php`
- `backend/resources/views/host/rooms/index.blade.php`
- `backend/resources/views/layouts/app.blade.php`
- `backend/resources/views/search/index.blade.php`
- `backend/routes/web.php`
- `backend/tests/Feature/M2BookingFlowTest.php`
- `docs/PROJECT_CONTEXT.md`
- `docs/CODE_STATUS.md`

## Database/schema changes
- No migrations were added or run.
- Implementation maps to existing Week 4 tables: `rooms`, `room_availability`, `seasonal_prices`, `bookings`, and `booking_status_logs`.

## Routes/endpoints added
- `GET /tim-kiem` -> `search.index`
- `GET /tim-kiem/ket-qua` -> `search.results`
- `POST /bookings` -> `bookings.store`
- `GET /chu-homestay/rooms` -> `host.rooms.index`
- `POST /chu-homestay/rooms` -> `host.rooms.store`
- `GET /chu-homestay/rooms/create` -> `host.rooms.create`
- `GET /chu-homestay/rooms/{room}/edit` -> `host.rooms.edit`
- `PUT/PATCH /chu-homestay/rooms/{room}` -> `host.rooms.update`

## Commands run
```bash
Get-Content -Raw AGENTS.md
Get-Content -Raw docs/PROJECT_CONTEXT.md
Get-Content -Raw docs/NEXT_TASK.md
Get-Content -Raw docs/08_NEXT_TASK_WEEK6_M2.md
Get-Content -Raw docs/CODE_STATUS.md
rg --files
Get-Content -Raw database/schema_DT07.sql
Get-Content -Raw backend/composer.json
Get-Content -Raw backend/routes/web.php
Get-Content -Raw backend/app/Models/User.php
Get-Content -Raw backend/app/Models/Homestay.php
Get-Content -Raw backend/app/Http/Controllers/Admin/HomestayController.php
Get-Content -Raw backend/tests/Feature/M1AuthRoleSmokeTest.php
Get-Content -Raw backend/phpunit.xml
Get-Content -Raw backend/app/Http/Kernel.php
Get-Content -Raw backend/app/Http/Controllers/DashboardController.php
Get-Content -Raw backend/resources/views/layouts/app.blade.php
Get-Content -Raw backend/.env
Get-Content -Raw backend/app/Http/Requests/StoreHomestayRequest.php
Get-Content -Raw backend/app/Policies/HomestayPolicy.php
Get-Content -Raw backend/app/Providers/AuthServiceProvider.php
Get-Content -Raw backend/database/seeders/DemoAccountSeeder.php
Get-ChildItem -Recurse app,tests -Filter *.php | ForEach-Object { C:\xampp\php\php.exe -l $_.FullName }
C:\xampp\php\php.exe artisan test --filter=M2BookingFlowTest
C:\xampp\php\php.exe artisan test --filter=M1AuthRoleSmokeTest
C:\xampp\php\php.exe artisan test
C:\xampp\php\php.exe artisan route:list --path=tim-kiem
C:\xampp\php\php.exe artisan route:list --path=bookings
C:\xampp\php\php.exe artisan route:list --path=chu-homestay/rooms
C:\xampp\php\php.exe -r "...DB record count script..."
rg --files -g '*.sql' -g '*seed*' -g '*.csv' -g '*.json'
git status --short
```

## Actual record counts
Configured DB connection: `dt07_homestay`.

| Table | Count |
|---|---:|
| homestays | 3 |
| rooms | 0 |
| room_availability | 0 |
| seasonal_prices | 0 |
| bookings | 0 |
| local_products | 0 |
| product_orders | 0 |
| experiences | 0 |
| experience_bookings | 0 |
| reviews | 0 |
| tourism_total | 3 |

Result: the runnable DB does not currently meet the M2 data-proof threshold of >=300 tourism records. The repo snapshot only contains `database/schema_DT07.sql`; no prepared seed SQL/CSV/JSON dataset was found.

## Tests and checks actually executed
| Test/check | Result |
|---|---|
| PHP syntax check over `backend/app` and `backend/tests` | Passed: no syntax errors. |
| `php artisan test --filter=M2BookingFlowTest` | Passed: 9 tests, 9 passed. |
| `php artisan test --filter=M1AuthRoleSmokeTest` | Passed: 5 tests, 5 passed. |
| `php artisan test` | Passed: 16 tests, 16 passed. |
| `route:list` for M2 paths | Passed: new search, booking, and host room routes are registered. |

## Concurrency-test method/result
True parallel HTTP execution was not used in the local feature test harness. The implemented proof is at the service/transaction level: two booking attempts target the same last available unit; the first booking commits, the second raises `AvailabilityConflictException`, and only one booking row exists for that room/date. The production code uses `DB::transaction()` and `lockForUpdate()` on `room_availability` rows ordered by `stay_date`.

## Known issues / unfinished work
- The prepared 3,850-record tourism dataset is not present in this repository snapshot and is not loaded in the configured local DB; data proof remains incomplete.
- `backend/.env` currently shows `SESSION_DRIVER=file`, while the previous M1 handoff said database sessions were enabled. Re-check the student's local `.env` before capturing session-storage evidence.
- Host room CRUD currently covers room fields only. A richer availability/calendar editing screen can be added next; M2 availability behavior is currently exercised through data rows and booking/search tests.
- No browser screenshots were captured by Codex.
- No Git commit was created.

## Screenshot list to capture
- Public search page at `/tim-kiem`.
- Search results at `/tim-kiem/ket-qua?location=...&checkin=...&checkout=...&guests=...` after loading rooms/availability.
- Guest successful booking response or redirect after clicking `Đặt phòng`.
- Host room list at `/chu-homestay/rooms`.
- Host create/edit room validation errors.
- Host A attempting to update Host B's room and receiving 403.
- Terminal output for `C:\xampp\php\php.exe artisan test --filter=M2BookingFlowTest`.
- Terminal output for `C:\xampp\php\php.exe artisan test`.
- Database query showing `room_availability.units_sold` incremented and `booking_status_logs` row written after a booking.

## Git evidence to capture
- `git status --short`
- `git diff -- backend docs/PROJECT_CONTEXT.md docs/CODE_STATUS.md`
- Real commit command/output if the student commits this M2 work.
