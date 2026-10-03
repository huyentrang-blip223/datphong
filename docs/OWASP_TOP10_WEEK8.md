# OWASP Top 10 Review Week 8

## Broken Access Control
- Files/routes: `routes/web.php`, `EnsureRole`, `RoomPolicy`, seller/host controllers.
- Check: PHPUnit M1/M2/Week8 permission tests.
- Result: Fixed High issue where room update validation did not scope posted `homestay_id` to owner. Seller products and host availability reject cross-owner access.
- Evidence: `Week8AvailabilityPricingTest`, `Week8OrderPaymentReviewTest`.

## Cryptographic Failures
- Files: `User`, `AuthController`, `config/hashing.php`.
- Check: M1 tests and source review.
- Result: Passwords use `password_hash` through Laravel Hash. No card data stored; payment is mock/test only.

## Injection
- Files: controllers/services use Eloquent query builder.
- Check: source review.
- Result: No raw SQL in controllers. `/healthz` uses constant `select 1` only. Search uses bound query builder expressions.

## Insecure Design
- Files: `BookingService`, `ProductOrderService`, `PaymentService`, `ReviewService`.
- Check: business-rule tests.
- Result: Booking and order stock use transactions/locks. Reviews require completed booking. Payment duplicate guard exists.

## Security Misconfiguration
- Files: `.env.example`, `config/services.php`, `VerifyCsrfToken`.
- Check: source review and git status.
- Result: CSRF remains enabled. Python token is configured by env. `.env` is not tracked.

## Vulnerable and Outdated Components
- Files: `composer.json`, `python-service/requirements.txt`.
- Check: version review only.
- Result: Laravel remains 9 by project instruction. No vulnerability scanner executed in this environment.

## Identification and Authentication Failures
- Files: `AuthController`, `M1AuthRoleSmokeTest`.
- Check: M1 regression.
- Result: Login/logout/session behavior tested. Stale logout token handled without 419.

## Software and Data Integrity Failures
- Files: `ProductOrderService`, `PaymentService`, `PythonDataService`.
- Check: source review.
- Result: Server computes order totals from DB price, not request price. Python service calls include internal token and fallback.

## Security Logging and Monitoring Failures
- Files: `BookingService`, `BookingController`, `PythonDataService`.
- Check: source review.
- Result: Booking status logs are written for create/payment/cancel. Python integration logs errors. Full audit log UI remains not implemented.

## SSRF
- Files: `PythonDataService`, `config/services.php`.
- Check: source review.
- Result: Laravel calls only configured `PY_SERVICE_URL`. No user-controlled URL fetch path found.

## Not Executed
No external vulnerability scanner or dependency audit was executed during this round.
