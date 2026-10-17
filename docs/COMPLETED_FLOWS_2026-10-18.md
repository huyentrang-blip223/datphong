# Missing-flow completion — 2026-10-18

Implemented flows and policies are described in PROJECT_CONTEXT section 16. No migrations or destructive database seed/reset commands are required for this change.

## Running locally

Keep the existing Laravel and Python start commands. In another terminal, from `backend`:

```powershell
C:\xampp\php\php.exe artisan schedule:work
```

This is needed for automatic expiry even when nobody visits the website. To run one sweep manually:

```powershell
C:\xampp\php\php.exe artisan bookings:expire
```

Docker Compose now contains a scheduler service, but Docker startup has not been verified here.

## Pages to try

- Guest: room booking -> 15-minute hold -> mock payment -> cancellation -> simulated refund. Unpaid/expired/cancelled bookings cannot be paid. Room review becomes available after the host completes a paid stay after checkout.
- Host: `/chu-homestay/homestays` -> register/edit -> submit private proof -> await admin review. Editing an approved property puts it back in pending.
- Admin: `/quan-tri/verifications` -> download proof -> approve/reject, with note required on rejection.
- Guest: `/tai-khoan/experience-bookings` -> history, payment, cancellation with capacity return.
- Host: `/chu-homestay/experience-bookings` -> attendee list and completion after end time.
- Guest/seller: product order cancellation before packing; stock returns and paid orders get simulated refunds. Points are awarded at delivery only.
- Admin dashboard: two Vue charts with metric selectors, CSV download; date range filters apply to check-in dates.

All refunds are academic ledger simulations, including records originally paid via VNPay sandbox. External money transfer/refund APIs are not implemented or claimed.

## Verification commands

```powershell
# From backend
C:\xampp\php\php.exe artisan test
C:\xampp\php\php.exe artisan test --filter=CompletedFlowsTest
C:\xampp\php\php.exe artisan route:list
C:\xampp\php\php.exe artisan schedule:list
Get-ChildItem app,tests -Recurse -Filter *.php | ForEach-Object { C:\xampp\php\php.exe -l $_.FullName }

# From python-service
python -m pytest -q

# From repository root, with Laravel running on port 8010
python e2e/verify_completed_flows.py --base-url http://127.0.0.1:8010
```

The UI checker uses Python Playwright and installed Google Chrome, existing demo accounts, six pages and three widths (390/768/1440). It does not submit bookings or modify business data. Its screenshots/results go into ignored `.appdata/ui-checks/`. Playwright was installed into ignored `.appdata/qa`, without changing global dependencies.

## Remaining manual verification

- Real VNPay sandbox redirect/callback with merchant credentials, including late settlements and reconciliation. Tests use signed simulated callbacks.
- Scheduler running continuously on the intended local/hosting machine.
- True simultaneous requests from separate database connections/processes: transaction/row locks and repeated/conflicting actions are covered by tests, but a load/concurrency benchmark is not claimed.
- Firefox/WebKit browser engines, Lighthouse on five pages, usability with five outside users, public HTTPS and database restore. These are separate evidence requirements from the handbook and remain unverified.
- Docker build/start is unverified; no deployment credentials have been supplied.
- Previously documented Composer dependency advisories were not resolved by this feature work. No fresh audit results are claimed.

## Executed results

- Final `php artisan test`: **73 passed**, 15.05 seconds. Includes 15 new flow tests, updated M2 holding/M3 pending expectations, plus existing regressions.
- `python -m pytest -q`: **5 passed**, two pre-existing deprecation warnings.
- PHP syntax checks: **123 app/test files passed**.
- Laravel Pint ran successfully on the changed PHP files.
- `route:list`: executed successfully; output captured locally in `.appdata/routes-completed-flows.txt`.
- `schedule:list`: executed successfully; `bookings:expire` runs every minute.
- Python Playwright with installed Chrome: **18 checks passed** across six authenticated pages and three viewport widths. Verified Vue chart mounting/metric selection, HTTP 200, no page JavaScript exceptions, and no page-wide horizontal overflow. Results and 18 screenshots are in `.appdata/ui-checks/`; that folder is ignored by Git.
- The temporary HTTP test server on port 8010 was stopped after verification.
- Initial attempts using unsupported test/route CLI flags were corrected (`--no-ansi` and `--compact` were not accepted by this Laravel version). An initial duplicate-review regression exposed a 500 response; the service now locks and rejects duplicate reviews with 422, and the final suite passed.
- No fresh MySQL benchmark, deployment, external payment or refund transfer results are claimed.

## Changed files

See the generated file inventory below. Existing `run.txt` commands were not changed.

- `backend/app/Console/Commands/ExpireBookingHolds.php`
- `backend/app/Console/Kernel.php`
- `backend/app/Http/Controllers/Admin/BookingReportController.php`
- `backend/app/Http/Controllers/Admin/PaymentOverviewController.php`
- `backend/app/Http/Controllers/Admin/VerificationController.php`
- `backend/app/Http/Controllers/BookingController.php`
- `backend/app/Http/Controllers/BookingLifecycleController.php`
- `backend/app/Http/Controllers/ExperienceBookingController.php`
- `backend/app/Http/Controllers/Host/HomestayController.php`
- `backend/app/Http/Controllers/PaymentController.php`
- `backend/app/Http/Controllers/SearchController.php`
- `backend/app/Http/Requests/ExportBookingsRequest.php`
- `backend/app/Http/Requests/ReviewVerificationRequest.php`
- `backend/app/Http/Requests/SaveOwnedHomestayRequest.php`
- `backend/app/Http/Requests/SearchRoomsRequest.php`
- `backend/app/Http/Requests/StoreHomestayRequest.php`
- `backend/app/Http/Requests/StoreProductOrderRequest.php`
- `backend/app/Http/Requests/UpdateHomestayRequest.php`
- `backend/app/Http/Requests/UpdateProductOrderStatusRequest.php`
- `backend/app/Models/Booking.php`
- `backend/app/Models/HomestayVerification.php`
- `backend/app/Services/BookingLifecycleService.php`
- `backend/app/Services/BookingReportService.php`
- `backend/app/Services/BookingService.php`
- `backend/app/Services/CommercePaymentService.php`
- `backend/app/Services/ExperienceBookingService.php`
- `backend/app/Services/HomestaySubmissionService.php`
- `backend/app/Services/LoyaltyService.php`
- `backend/app/Services/PaymentService.php`
- `backend/app/Services/ProductOrderService.php`
- `backend/app/Services/PythonDataService.php`
- `backend/app/Services/ReviewService.php`
- `backend/app/Services/RoomSearchService.php`
- `backend/app/Services/VnPayPaymentService.php`
- `backend/config/app.php`
- `backend/package.json`
- `backend/public/js/admin-charts.js`
- `backend/public/js/vendor/Vue-LICENSE`
- `backend/public/js/vendor/vue.runtime.esm-browser.prod.js`
- `backend/resources/views/admin/homestays/_form.blade.php`
- `backend/resources/views/admin/payments/index.blade.php`
- `backend/resources/views/admin/verifications/index.blade.php`
- `backend/resources/views/bookings/index.blade.php`
- `backend/resources/views/dashboard/admin.blade.php`
- `backend/resources/views/dashboard/host.blade.php`
- `backend/resources/views/experiences/bookings.blade.php`
- `backend/resources/views/host/bookings/index.blade.php`
- `backend/resources/views/host/homestays/form.blade.php`
- `backend/resources/views/host/homestays/index.blade.php`
- `backend/resources/views/layouts/app.blade.php`
- `backend/resources/views/orders/index.blade.php`
- `backend/resources/views/payments/show.blade.php`
- `backend/resources/views/search/index.blade.php`
- `backend/routes/web.php`
- `backend/tests/Feature/CompletedFlowsTest.php`
- `backend/tests/Feature/M2BookingFlowTest.php`
- `backend/tests/Feature/M3PythonIntegrationTest.php`
- `docker-compose.yml`
- `docs/CODE_STATUS.md`
- `docs/COMPLETED_FLOWS_2026-10-18.md`
- `docs/PROJECT_CONTEXT.md`
- `e2e/verify_completed_flows.py`
