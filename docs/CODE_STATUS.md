# CODE_STATUS.md — Handoff from Codex to ChatGPT

Updated at the end of this coding session: 2026-09-28.

## Current milestone
Week 5 / M1: locally runnable Laravel app with authentication, server-side role authorization, common Blade layout/navigation, `homestays` CRUD/ownership proof, and database-backed sessions.

## What was implemented
- Changed active app session storage from `SESSION_DRIVER=file` to `SESSION_DRIVER=database` in `backend/.env`.
- Generated Laravel's standard `sessions` table migration with `php artisan session:table`.
- Ran only the sessions migration path so the existing Week 4 schema was not recreated.
- Updated `backend/config/session.php` so `SESSION_ENCRYPT` and `SESSION_SAME_SITE` from `.env` are honored.
- Seeded demo accounts and verified login/logout over HTTP using Laravel CSRF tokens and cookies.
- Reran `M1AuthRoleSmokeTest` and the full test suite.

## Files changed
- `backend/.env`
- `backend/config/session.php`
- `backend/database/migrations/2026_09_27_202620_create_sessions_table.php`
- `docs/CODE_STATUS.md`

## Database/schema changes
- Added Laravel `sessions` table to the configured MySQL database `dt07_homestay`.
- Laravel `migrations` table was created because it was missing.
- `migrate:status` showed:
  - `2026_09_27_202620_create_sessions_table` => ran in batch 1
  - starter Laravel migrations for `users`, `password_resets`, and `failed_jobs` remain pending because the project uses the existing Week 4 schema.

## API changes
- None.

## Commands run
```bash
Get-Content -LiteralPath AGENTS.md
Get-Content -LiteralPath docs\PROJECT_CONTEXT.md
Get-Content -LiteralPath docs\NEXT_TASK.md
Get-Content -LiteralPath docs\CODE_STATUS.md
Get-Content -LiteralPath backend\config\session.php
Get-ChildItem -LiteralPath backend\database\migrations | Select-Object -ExpandProperty Name
Get-Content -LiteralPath backend\.env
C:\xampp\php\php.exe artisan migrate:status
C:\xampp\php\php.exe artisan session:table
Get-ChildItem -LiteralPath backend\database\migrations | Select-Object -ExpandProperty Name
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe -l config\session.php
C:\xampp\php\php.exe -l database\migrations\2026_09_27_202620_create_sessions_table.php
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2026_09_27_202620_create_sessions_table.php
C:\xampp\php\php.exe artisan migrate:status
C:\xampp\php\php.exe artisan tinker --execute="dump(config('session.driver')); dump(\Illuminate\Support\Facades\Schema::hasTable('sessions'));"
C:\xampp\php\php.exe artisan db:seed --class=DemoAccountSeeder
C:\xampp\php\php.exe -r "require 'vendor/autoload.php'; `$app = require 'bootstrap/app.php'; `$kernel = `$app->make(Illuminate\Contracts\Console\Kernel::class); `$kernel->bootstrap(); echo config('session.driver') . PHP_EOL; echo Illuminate\Support\Facades\Schema::hasTable('sessions') ? 'sessions_table=yes' . PHP_EOL : 'sessions_table=no' . PHP_EOL;"
C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000
C:\xampp\php\php.exe -r "require 'vendor/autoload.php'; `$app = require 'bootstrap/app.php'; `$kernel = `$app->make(Illuminate\Contracts\Console\Kernel::class); `$kernel->bootstrap(); echo Illuminate\Support\Facades\DB::table('sessions')->count() . PHP_EOL;"
Invoke-WebRequest login/logout check without -UseBasicParsing
Invoke-WebRequest login/logout check with -UseBasicParsing
Invoke-WebRequest guest admin redirect check
C:\xampp\php\php.exe artisan test --filter=M1AuthRoleSmokeTest
C:\xampp\php\php.exe artisan test
Get-Content -LiteralPath backend\database\migrations\2026_09_27_202620_create_sessions_table.php
git status --short
Get-Content -LiteralPath backend\.env | Select-String -Pattern 'SESSION_DRIVER|SESSION_ENCRYPT|SESSION_SECURE_COOKIE|SESSION_SAME_SITE'
```

## Tests and checks actually executed
| Test/check | Result |
|---|---|
| Initial `migrate:status` | Failed as expected before setup: migration table not found. |
| `php artisan session:table` | Passed: migration created. |
| `php artisan config:clear` | Passed. |
| `php -l config/session.php` | Passed: no syntax errors. |
| `php -l database/migrations/2026_09_27_202620_create_sessions_table.php` | Passed: no syntax errors. |
| `php artisan migrate --path=database/migrations/2026_09_27_202620_create_sessions_table.php` | Passed: migration table created and sessions migration ran. |
| `php artisan migrate:status` after migration | Passed: sessions migration is ran; older starter migrations are pending. |
| `php artisan tinker --execute=...` | Failed: PsySH attempted to write to `C:/Users/Admin/AppData/Roaming/PsySH`, which is outside sandbox permissions. |
| Bootstrapped PHP config/table check | Passed: output was `database` and `sessions_table=yes`. |
| `php artisan db:seed --class=DemoAccountSeeder` | Passed. |
| Database session count before HTTP login/logout | Passed: output was `0`. |
| First PowerShell login/logout HTTP attempt | Failed: missing `-UseBasicParsing` prevented CSRF token parsing and caused Laravel 419 responses. |
| Second PowerShell login/logout HTTP attempt with `-UseBasicParsing` | Passed: login `302` to `/quan-tri/bang-dieu-khien`, dashboard `200`, logout `302` to `/dang-nhap`. |
| Fresh guest request to `/quan-tri/bang-dieu-khien` | Passed: `302` to `/dang-nhap`. |
| Database session count after HTTP checks | Passed: output was `3`, proving rows are written to `sessions`. |
| `php artisan test --filter=M1AuthRoleSmokeTest` | Passed: 5 tests, 5 passed. |
| `php artisan test` | Passed: 7 tests, 7 passed. |

## Runtime evidence available
- Local URL used during verification: `http://127.0.0.1:8000`
- Screenshots: None captured by Codex.
- Git commit: None; `git status --short` failed because neither `C:\xampp\htdocs\source_m1` nor `backend` is a Git repository in this terminal.
- Other: HTTP login/logout statuses and PHPUnit outputs above.

## Known issues / unfinished work
- `backend/composer.json` is Laravel `^9.19`, while project documentation says Laravel 11 is the preferred stack. This was intentionally not changed in this task.
- The starter Laravel migrations remain pending in `migrate:status`; do not run the full migration stack against the existing Week 4 schema unless the team first baselines or converts the schema properly.
- Manual browser screenshots and Git evidence still need to be captured from the student's actual repo/browser.

## Decisions that documentation must reflect
- Active Laravel app now uses database sessions: `SESSION_DRIVER=database`.
- Session config now reads `SESSION_ENCRYPT` and `SESSION_SAME_SITE` from `.env`.
- Sessions table was added through Laravel's standard session migration, not by changing the Week 4 tourism schema.
- Tests still use PHPUnit's configured `SESSION_DRIVER=array`; database-session behavior was verified separately through real HTTP requests to the local dev server.

## Exact final M1 evidence to capture
- Screenshot 1: Login page at `/dang-nhap`.
- Screenshot 2: Successful admin login landing on `/quan-tri/bang-dieu-khien`.
- Screenshot 3: Admin homestay CRUD index at `/quan-tri/homestays`.
- Screenshot 4: Admin create homestay form, plus validation error after submitting an invalid/empty required field.
- Screenshot 5: Admin edit homestay form after saving a visible update.
- Screenshot 6: Host directly accessing `/quan-tri/bang-dieu-khien` or `/quan-tri/homestays` and receiving `403`.
- Screenshot 7: Seller directly accessing `/chu-homestay/bang-dieu-khien` and receiving `403`.
- Screenshot 8: Unauthenticated access to `/chu-homestay/bang-dieu-khien` or `/quan-tri/bang-dieu-khien` redirecting to `/dang-nhap`.
- Screenshot 9: Host A directly accessing `/chu-homestay/homestays/{HostBHomestayId}/edit` and receiving `403`.
- Screenshot 10: Browser DevTools Application/Cookies showing the app session cookie with `HttpOnly` and `SameSite=Lax` (`Secure` is false on local HTTP; use HTTPS if you need to show Secure=true).
- Screenshot 11: Database view/query showing rows in `sessions`.
- Screenshot 12: Database view/query showing `users.password_hash` values are hashed (`$2y$` bcrypt or argon2id prefix), not plaintext.
- Screenshot 13: Terminal output for `C:\xampp\php\php.exe artisan test --filter=M1AuthRoleSmokeTest`.
- Screenshot 14: Terminal output for `C:\xampp\php\php.exe artisan test`.

## Git evidence to capture
- `git status --short` showing intended changed files before commit.
- `git diff -- backend/.env backend/config/session.php backend/database/migrations/2026_09_27_202620_create_sessions_table.php docs/CODE_STATUS.md` or equivalent staged diff.
- `git add ...` command or staging screenshot.
- `git commit -m "Complete M1 database sessions and smoke verification"` output with the real commit hash.
- `git log --oneline -5` showing the M1 commit.

## Next coding task
- Capture the browser/database/Git evidence listed above and package it with the M1 report.
