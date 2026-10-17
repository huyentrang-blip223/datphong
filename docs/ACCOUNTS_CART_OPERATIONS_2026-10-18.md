# Account/cart/operations delivery — 2026-10-18

## Implemented

- Password recovery, password change, editable profile and immediate server-side account lock enforcement.
- Admin account creation, role changes, lock/unlock and safeguards for administrators/resource owners.
- Multi-product cart with automatic seller split, atomic checkout and duplicate submission protection.
- Point redemption with per-order cap, discounted totals, cancellation restore and accurate spendable balance.
- VNPay IPN, sandbox refund/query, pending-refund reconciliation and late-settlement recording.
- Byte-safe backup/restore scripts, an installed local scheduler task and Render deployment assets.

See PROJECT_CONTEXT section 17 for exact policies and RENDER_DEPLOYMENT for hosting setup.

## Database migration commands executed

```powershell
# From backend; both completed successfully without resetting existing tables/data
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2026_10_18_000001_complete_accounts_and_cart.php --force
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2026_10_18_000002_add_gateway_refund_tracking.php --force
```

## Verification

The feature suite includes negative password/current-password checks, expired/single-use tokens, locked accounts, role escalation attempts, owned resources, checkout idempotency, multi-seller rollback, point caps/balance/cancellation, gateway signatures and uncertain refunds. HTTP gateway/email tests use controlled responses, without sending live mail or transferring money.

Commands used:

```powershell
# backend
C:\xampp\php\php.exe artisan test
C:\xampp\php\php.exe artisan test --filter=AccountCartTest
C:\xampp\php\php.exe artisan test --filter=VnPayGatewayTest
C:\xampp\php\php.exe artisan route:list
C:\xampp\php\php.exe artisan schedule:list

# repository root
python scripts/db-maintenance.py backup
python scripts/db-maintenance.py restore-test --backup-file <the-created-backup.sql>
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/install-scheduler.ps1
Get-ScheduledTaskInfo -TaskName DT07-Homestay-Scheduler
python e2e/verify_completed_flows.py --base-url http://127.0.0.1:8010

# python-service
python -m pytest -q
```

- Final Laravel full suite: **91 passed** in 17.15 seconds, including an additional missed-IPN reconciliation case.
- Python **6 passed**, two existing deprecation warnings, including a URL-credential decoding regression.
- **153 PHP app/test/migration files** passed syntax checks; changed PHP formatted with Laravel Pint; Render/Docker YAML parsed successfully.
- **30 Chrome UI checks** passed across ten authenticated pages at 390/768/1440 px. Charts mounted and changed metrics, HTTP responses were 200, no page JavaScript exceptions and no page-wide horizontal overflow. Screenshots/results are in ignored `.appdata/ui-checks/`.
- Restore-test passed against 29 tables/views before the gateway migration; all row counts matched. Backup files are ignored by Git. A fresh final backup/restore is recorded below if rerun.
- Windows scheduled task successfully executed with LastTaskResult=0 and a next run every minute. It uses an interactive limited principal, not stored administrator credentials.
- Render initialization on an isolated database: 30 tables/views and two triggers, then a second startup without duplicate admin creation. The disposable database was removed afterward.
- Deployment demo initialization also passed with 3,240 future availability rows; fresh demo seeding is explicitly opt-in and never run on the existing application database.
- Final backup/restore after both migrations: PASS, all 29 table/view counts matched, five migration records present. The generated restore database was removed and the original database was not overwritten.
- Actual VNPay sandbox redirect probe returned HTTP 200 on its error page with **Website này chưa được phê duyệt**. This proves contact with the sandbox, not a successful payment. Test fixture records were rolled back.
- Docker CLI was found, but Docker Desktop returned unable to start. Docker build/compose/Render container startup are not reported as passed.

## What still needs the user's external account/configuration

- Install/connect Render or perform the documented Blueprint steps, provide an external MySQL 8 connection and choose durable storage for uploads.
- Approved VNPay sandbox merchant and registration of the actual HTTPS IPN/return URLs.
- Email API credentials and verified sender for real password recovery delivery.
- Verify public HTTPS with `scripts/check-https.py` after Render issues the real URL. No guessed or nonexistent site URL is supplied.

## File inventory

The inventory below includes the earlier missing-flow work because it had not yet been committed before this extension.

- `.dockerignore`
- `.gitignore`
- `backend/.env.example`
- `backend/.env.production.example`
- `backend/app/Console/Commands/ExpireBookingHolds.php`
- `backend/app/Console/Commands/PrepareDeployment.php`
- `backend/app/Console/Kernel.php`
- `backend/app/Http/Controllers/AccountController.php`
- `backend/app/Http/Controllers/Admin/BookingReportController.php`
- `backend/app/Http/Controllers/Admin/PaymentOverviewController.php`
- `backend/app/Http/Controllers/Admin/PaymentReconciliationController.php`
- `backend/app/Http/Controllers/Admin/UserController.php`
- `backend/app/Http/Controllers/Admin/VerificationController.php`
- `backend/app/Http/Controllers/AuthController.php`
- `backend/app/Http/Controllers/BookingController.php`
- `backend/app/Http/Controllers/BookingLifecycleController.php`
- `backend/app/Http/Controllers/CartController.php`
- `backend/app/Http/Controllers/ExperienceBookingController.php`
- `backend/app/Http/Controllers/Host/HomestayController.php`
- `backend/app/Http/Controllers/LoyaltyController.php`
- `backend/app/Http/Controllers/PaymentController.php`
- `backend/app/Http/Controllers/ProductOrderController.php`
- `backend/app/Http/Controllers/SearchController.php`
- `backend/app/Http/Kernel.php`
- `backend/app/Http/Middleware/EnsureActiveAccount.php`
- `backend/app/Http/Middleware/TrustProxies.php`
- `backend/app/Http/Requests/CartCheckoutRequest.php`
- `backend/app/Http/Requests/CartItemRequest.php`
- `backend/app/Http/Requests/ChangePasswordRequest.php`
- `backend/app/Http/Requests/ExportBookingsRequest.php`
- `backend/app/Http/Requests/PasswordRecoveryRequest.php`
- `backend/app/Http/Requests/ReviewVerificationRequest.php`
- `backend/app/Http/Requests/SaveOwnedHomestayRequest.php`
- `backend/app/Http/Requests/SaveUserRequest.php`
- `backend/app/Http/Requests/SearchRoomsRequest.php`
- `backend/app/Http/Requests/StoreHomestayRequest.php`
- `backend/app/Http/Requests/StoreProductOrderRequest.php`
- `backend/app/Http/Requests/UpdateHomestayRequest.php`
- `backend/app/Http/Requests/UpdateProductOrderStatusRequest.php`
- `backend/app/Http/Requests/UpdateProfileRequest.php`
- `backend/app/Mail/ResendTransport.php`
- `backend/app/Models/Booking.php`
- `backend/app/Models/HomestayVerification.php`
- `backend/app/Models/Payment.php`
- `backend/app/Models/ProductOrder.php`
- `backend/app/Models/User.php`
- `backend/app/Notifications/ResetAccountPassword.php`
- `backend/app/Providers/AppServiceProvider.php`
- `backend/app/Providers/RouteServiceProvider.php`
- `backend/app/Services/AccountService.php`
- `backend/app/Services/BookingLifecycleService.php`
- `backend/app/Services/BookingReportService.php`
- `backend/app/Services/BookingService.php`
- `backend/app/Services/CartService.php`
- `backend/app/Services/CommercePaymentService.php`
- `backend/app/Services/ExperienceBookingService.php`
- `backend/app/Services/FoundationSchemaService.php`
- `backend/app/Services/HomestaySubmissionService.php`
- `backend/app/Services/LoyaltyService.php`
- `backend/app/Services/NotificationService.php`
- `backend/app/Services/PaymentService.php`
- `backend/app/Services/ProductOrderService.php`
- `backend/app/Services/PythonDataService.php`
- `backend/app/Services/ReviewService.php`
- `backend/app/Services/RoomSearchService.php`
- `backend/app/Services/UserAdministrationService.php`
- `backend/app/Services/VnPayGatewayService.php`
- `backend/app/Services/VnPayPaymentService.php`
- `backend/config/app.php`
- `backend/config/loyalty.php`
- `backend/config/mail.php`
- `backend/config/services.php`
- `backend/database/migrations/2014_10_12_000000_create_users_table.php`
- `backend/database/migrations/2014_10_12_100000_create_password_resets_table.php`
- `backend/database/migrations/2026_09_27_202620_create_sessions_table.php`
- `backend/database/migrations/2026_10_18_000001_complete_accounts_and_cart.php`
- `backend/database/migrations/2026_10_18_000002_add_gateway_refund_tracking.php`
- `backend/database/seeders/DeploymentDemoSeeder.php`
- `backend/package.json`
- `backend/public/js/admin-charts.js`
- `backend/public/js/vendor/Vue-LICENSE`
- `backend/public/js/vendor/vue.runtime.esm-browser.prod.js`
- `backend/resources/views/account/profile.blade.php`
- `backend/resources/views/admin/homestays/_form.blade.php`
- `backend/resources/views/admin/payments/index.blade.php`
- `backend/resources/views/admin/users/form.blade.php`
- `backend/resources/views/admin/users/index.blade.php`
- `backend/resources/views/admin/verifications/index.blade.php`
- `backend/resources/views/auth/forgot-password.blade.php`
- `backend/resources/views/auth/login.blade.php`
- `backend/resources/views/auth/reset-password.blade.php`
- `backend/resources/views/bookings/index.blade.php`
- `backend/resources/views/cart/index.blade.php`
- `backend/resources/views/dashboard/admin.blade.php`
- `backend/resources/views/dashboard/host.blade.php`
- `backend/resources/views/experiences/bookings.blade.php`
- `backend/resources/views/host/bookings/index.blade.php`
- `backend/resources/views/host/homestays/form.blade.php`
- `backend/resources/views/host/homestays/index.blade.php`
- `backend/resources/views/layouts/app.blade.php`
- `backend/resources/views/loyalty/index.blade.php`
- `backend/resources/views/orders/index.blade.php`
- `backend/resources/views/payments/product_order.blade.php`
- `backend/resources/views/payments/show.blade.php`
- `backend/resources/views/products/show.blade.php`
- `backend/resources/views/search/index.blade.php`
- `backend/routes/api.php`
- `backend/routes/web.php`
- `backend/tests/Feature/AccountCartTest.php`
- `backend/tests/Feature/CompletedFlowsTest.php`
- `backend/tests/Feature/M2BookingFlowTest.php`
- `backend/tests/Feature/M3PythonIntegrationTest.php`
- `backend/tests/Feature/VnPayGatewayTest.php`
- `database/schema_DT07.sql`
- `docker-compose.https.yml`
- `docker-compose.yml`
- `docker/caddy/Caddyfile`
- `docker/php/Dockerfile`
- `docker/render/Dockerfile`
- `docker/render/nginx.conf`
- `docker/render/php-fpm.conf`
- `docker/render/start.sh`
- `docker/render/supervisord.conf`
- `docs/ACCOUNTS_CART_OPERATIONS_2026-10-18.md`
- `docs/CODE_STATUS.md`
- `docs/COMPLETED_FLOWS_2026-10-18.md`
- `docs/PROJECT_CONTEXT.md`
- `docs/RENDER_DEPLOYMENT.md`
- `e2e/verify_completed_flows.py`
- `python-service/app/db.py`
- `python-service/tests/test_db_connection.py`
- `render.yaml`
- `scripts/backup-db.ps1`
- `scripts/check-https.py`
- `scripts/db-connection.php`
- `scripts/db-maintenance.py`
- `scripts/install-scheduler.ps1`
- `scripts/restore-db-test.ps1`
