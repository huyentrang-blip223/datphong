# PROJECT_CONTEXT.md — ĐT-07

## 1. Project identity
ĐT-07: **Sàn lưu trú cộng đồng homestay gắn với sản phẩm địa phương**  
Course: CSE703073 — Lập trình ứng dụng web trong du lịch 2.

This file is the bridge between the documentation work prepared in ChatGPT and implementation work performed by Codex in VS Code.

## 2. Product positioning
The product is not intended to compete with Airbnb/Booking/Traveloka on catalogue size.

Its intended differentiation is:
- community homestay accommodation as the core
- local product storefront linked directly to each homestay
- cultural experiences linked to the stay
- a simple mobile-first host dashboard
- loyalty/review/notification features around the core booking flow

## 3. Personas carried from Week 2
### P1 — Minh Anh
Young traveler, price-sensitive, mobile-first, wants transparent final price, real availability, reliable reviews, and local activities/products in one place.

### P2 — Thu Trang
Family traveler, prioritizes safety, clear policies, verified information, family amenities, easy confirmation management.

### P3 — Chị Lan
Homestay owner with limited digital skills. Needs a very simple mobile-first dashboard, clear room calendar, fast seasonal price updates, and easy booking/product/experience management.

## 4. Requirements status
Previously defined:
- 22 functional requirements (FR)
- 12 non-functional requirements (NFR)
- 4 main actors: guest, host, seller, admin

Must-have direction:
- homestay registration/verification
- room/calendar/pricing management
- search/filter
- anti-double-booking flow
- local products
- experiences
- review
- administration

## 5. Database status
Week 3/4 database design has 21 tables and keeps the following important entities:

- users
- homestays
- homestay_verifications
- rooms
- amenities
- room_amenities
- room_availability
- seasonal_prices
- bookings
- booking_status_logs
- payments
- local_products
- homestay_products
- product_orders
- product_order_items
- experiences
- experience_bookings
- reviews
- loyalty_transactions
- notifications
- audit_logs

Data preparation state:
- 12 homestays
- 36 rooms
- 3240 room_availability rows
- 72 seasonal_prices
- 180 bookings
- 60 local_products
- 80 product_orders
- 24 experiences
- 67 experience_bookings
- 79 reviews
- total main tourism records: 3850

A Python data validation script previously reported 40/40 checks in the prepared dataset.

Important: real MySQL EXPLAIN ANALYZE benchmark results still need to be produced on the student's actual environment. Do not fabricate them.

## 6. API design status
Week 4 prepared an API v0.1 and an OpenAPI YAML.
Current design direction:
- REST
- prefix `/api/v1`
- role + ownership authorization
- standardized validation/errors
- pagination/filtering
- booking availability conflicts should map to HTTP 409

Keep code aligned with the existing OpenAPI file if it is present in the repo.

## 7. UI direction
Week 4 prepared:
- sitemap/wireframes
- guest/host/admin flows
- design system
- two themes
- responsive target widths around 390 / 768 / 1440
- host workflow is mobile-first and low-complexity

## 8. Architecture direction
Use:
- Laravel 11 backend
- MySQL 8
- Vue 3 + Vite when implementing richer frontend
- Python/FastAPI module in a later milestone

Application should remain layered:
presentation -> business/service -> data access.

Laravel session is intended to use database storage so state can be inspected.

## 9. Week 5 / M1
Official milestone target:
- runnable system skeleton
- authentication
- authorization
- common layout/navigation
- basic data operation
- one managed entity

Implementation choice:
- `homestays` is the Week 5 CRUD entity.

M1 RBAC scenarios to implement/test:
1. admin -> `/quan-tri/homestays` => allowed (200)
2. host -> admin homestay route => denied (403)
3. unauthenticated -> host dashboard => redirect to login
4. seller -> host dashboard => denied (403)
5. host A -> edit Host B homestay => denied by ownership policy (403)

These are target cases. They are not considered executed until the code is actually run.

## 10. Next milestone
Week 6 / M2 should build the first major business flow:
- room management
- availability/calendar
- seasonal pricing
- homestay search/filter
- booking flow
- transaction/row lock to prevent double booking
- load/use the prepared tourism data

Do not jump to Week 6 until M1 is runnable and minimally verified.

## 11. Week 6 / M2 implementation status
The Laravel 9 app now has a first M2 business-flow implementation aligned with the Week 4 schema:
- host room CRUD routes under `/chu-homestay/rooms`
- public search routes under `/tim-kiem` and `/tim-kiem/ket-qua`
- guest booking route at `POST /bookings`
- `BookingService` for quote calculation, availability checks, booking creation, status logs, and transaction/row-level locking with deterministic stay-date ordering
- price precedence implemented as `room_availability.price_override` -> `seasonal_prices.price_per_night` -> `rooms.base_price`
- checkout date remains exclusive for nightly calculations
- normal availability conflict returns HTTP 409 for JSON/API-style requests

Current local database evidence from the configured `dt07_homestay` connection now shows 583 main tourism records after running `M2TourismDatasetSeeder`, which meets the >=300 M2 evidence threshold. This is a deterministic M2 demo dataset, not the previously prepared 3,850-record target dataset.

## 12. Week 7 / M3 implementation status
M3 extends the project with the second core business module and an integrated Python data service:
- local product browsing/filter/detail pages at `/san-pham-dia-phuong`
- product detail recommendations rendered on the Laravel UI through FastAPI, with Laravel fallback when Python is unavailable
- cultural experience listing/detail pages at `/trai-nghiem`
- guest experience booking with capacity checks and HTTP 409 conflict for normal capacity conflicts
- admin dashboard includes homestay/room/booking/product counts plus Python analytics for seasonality and top homestays
- `PythonDataService` uses timeout, retry, 30-minute cache, logging, token header, and Eloquent fallback
- FastAPI endpoints: `/healthz`, `/recommend/local-products/{product_id}`, `/analytics/seasonality`, `/analytics/top-homestays`, `/cache/refresh`
- ETL script cleans local product CSV input, writes clean CSV and stats JSON, and logs before/after counts
- runtime data evidence remains 583 main tourism records, meeting the >=300 threshold
- Python tests, Laravel M1/M2/M3 tests, and the full Laravel suite were executed successfully on 2026-10-02

Known limitation: the runtime dataset is deterministic synthetic/project-seed data, not the previously documented 3,850-record target dataset. Seller CRUD for products remains outside this M3 implementation; browsing/recommendation/product data integration were prioritized.

## 13. Week 8 implementation status
Week 8 Round 1 extends M3 without upgrading Laravel:
- host availability/calendar UI and service at `/chu-homestay/availability`;
- host seasonal pricing UI and service at `/chu-homestay/seasonal-prices`;
- host cultural experience CRUD at `/chu-homestay/experiences` with ownership checks;
- seller local product CRUD at `/nguoi-ban/products` with seller ownership checks;
- guest product order creation at `/tai-khoan/orders`, server-side DB pricing, stock check and transaction;
- seller order state transition guard for valid product-order states;
- mock/test booking payment with success/fail and duplicate-paid guard;
- guest booking history and cancel flow with availability rollback;
- guest review creation limited to completed own bookings;
- booking payment checkout page under `/tai-khoan/bookings/{booking}/payment`, linked after booking creation and from booking history;
- product order payment checkout page under `/tai-khoan/orders/{order}/payment`, with separate `product_order_payments` records;
- experience booking payment checkout page under `/tai-khoan/experience-bookings/{experienceBooking}/payment`, with separate `experience_booking_payments` records;
- VNPay sandbox payment initiation for room bookings from booking checkout, with callback at `/api/v1/payment/vn-pay-callback`;
- admin payment overview at `/quan-tri/payments` and audit-log browser at `/quan-tri/audit-logs`;
- email notifications for business events related to room bookings, booking payments/cancellations, product orders, product-order payment/status changes, and experience payments when SMTP is configured;
- `/healthz` application/database/Python status endpoint without secrets;
- two UI themes (`light`, `forest`) with CSS variables, `data-theme`, toggle and `localStorage` persistence;
- shared academic footer and responsive CSS for 390/768/1440 targets.

Week 8 documentation added:
- `docs/WEEK8_FUNCTION_GAP_MATRIX.md`;
- `docs/RESPONSIVE_THEME_WEEK8.md`;
- `docs/TEST_ROUND1_WEEK8.csv`;
- `docs/TEST_ROUND1_WEEK8_SUMMARY.md`;
- `docs/BUG_LOG_WEEK8.csv`;
- `docs/BUG_SUMMARY_WEEK8.md`;
- `docs/BROWSER_MATRIX_WEEK8.csv`;
- `docs/PERFORMANCE_WEEK8.csv`;
- `docs/OWASP_TOP10_WEEK8.md`;
- `docs/CHUONG_3_TUAN8_NHAP.md`;
- Mermaid diagram sources in `docs/diagrams/`.

Executed regression evidence on 2026-10-03:
- M1: 6 passed.
- M2: 9 passed.
- M3: 6 passed.
- Week8: 13 passed.
- Full Laravel suite after Week8: see `CODE_STATUS.md`.
- Python: 5 passed, 2 warnings.

Round 1 test matrix status:
- 60 cases designed.
- 51 marked PASS based on executed automated tests/source review.
- 9 marked NOT EXECUTED because browser/manual direct checks were not executed.

Known Week 8 limitations:
- Node/npm/npx are unavailable in PATH, so Playwright browser matrix and Lighthouse could not be executed; docs mark these as BLOCKED/NOT EXECUTED.
- Notifications and loyalty UI remain not implemented after Must features.
- Diagram PNG/SVG rendering was not executed; Mermaid source files are present only.

## 14. Week 9 / Code Freeze status
Week 9 completed additional code-critical carry-over from Week 8:
- notification model/service/pages under `/tai-khoan/notifications`;
- loyalty transaction model/service/page under `/tai-khoan/loyalty`;
- audit log service writes for booking, payment, order status and review events;
- host related-booking page under `/chu-homestay/bookings`;
- login rate limiting using Laravel RateLimiter;
- public guest-only registration with hashed passwords; elevated roles are not self-selectable;
- homestay cover image upload from local computer for admin homestay create/update, stored on Laravel public disk;
- security headers middleware and matching Nginx headers;
- friendly `403`, `404`, `419`, `500` Blade error pages;
- Laravel `/healthz` now returns `application`, `database`, `python`, and `timestamp`.

Deployment readiness package prepared:
- production env examples for Laravel and Python;
- Docker Compose with `nginx`, `app`, `mysql`, `python`;
- Nginx production-ready front-controller config;
- PowerShell Docker helper scripts;
- database backup and restore-test scripts;
- README, deployment options, monitoring runbook, demo flow, SUS/usability templates, and test account template.

Round 2 verification on 2026-10-03:
- M1: 10 passed.
- M2: 9 passed.
- M3: 6 passed.
- Week8: 15 passed.
- Week9: 8 passed.
- Full Laravel suite: 50 passed.
- Python: 5 passed, 2 warnings.

Code freeze status: **NOT READY**.
Reasons:
- `composer audit` found 16 advisories affecting 7 packages, including High advisories in Laravel/Symfony dependencies. Laravel major upgrade is out of scope by task instruction, so this remains a documented blocker.
- Docker is not available in PATH, so Docker build/up/health were not executed.
- `mysql` and `mysqldump` are not available in PATH, so backup/restore scripts were prepared but not executed.
- Node/npm/npx are unavailable, so browser matrix and Lighthouse remain blocked/not executed.
- No hosting/domain credentials are provided, so go-live is blocked and not deployed.

## 15. Admin media management update
Admin navigation now exposes management links for rooms and local products:
- `/quan-tri/rooms` supports admin create/update/delete for rooms across all homestays.
- `/quan-tri/products` supports admin create/update/delete for local products across all sellers.
- Room and local product forms support image upload through Laravel public disk storage.
- Uploaded room images are shown on the homepage featured room cards, room search/listing cards, and room detail gallery.
- Uploaded product images are shown on the homepage product cards, product listing/category page, product detail page, and room detail related-product cards.

Schema extension:
- `rooms.image_path` nullable string.
- `local_products.image_path` nullable string.
- `product_order_payments` stores product-order payment attempts separately from room-booking payments.
- `experience_booking_payments` stores experience-booking payment attempts separately from room-booking payments.

Deletion is intentionally guarded by database constraints: records with related bookings, availability, orders, or other linked data should be hidden/deactivated instead of force-deleted.

## 16. Completion of missing flows — 2026-10-18

This section supersedes earlier descriptions of immediate booking confirmation and missing host/notification/loyalty features. The running Laravel 9 stack is preserved; no database schema reset or framework replacement was performed.

- New room bookings enter `holding` for 15 minutes and consume `units_held`. Successful payment atomically moves one held unit to sold for every night, then sets `confirmed`. Existing confirmed records remain supported.
- `bookings:expire` releases expired holds in transactions with booking and date-ordered inventory locks. Laravel schedules it every minute; Docker Compose includes a scheduler service. Searching, creating a booking and opening checkout also clean up overdue holds. Local operation requires `php artisan schedule:work` for expiry without user activity.
- Paid room bookings can be cancelled at least 24 hours before the check-in date at midnight (application timezone Asia/Ho_Chi_Minh). Unpaid bookings can be cancelled before that date. Terminal booking cancellation is idempotent and cannot release another booking's inventory.
- Guest/admin refund actions only accept cancelled, paid bookings. Full refunds update the **academic sandbox ledger only**, reverse associated loyalty points and write audit/status/notification records. They never transfer money or call a gateway refund API. Booking checkout and refund buttons disclose this.
- Host completion is ownership-scoped and allowed only for paid confirmed bookings on/after the checkout date. It enables the existing completed-booking-only review form. Duplicate review submission returns a validation error, and homestay average rating is recalculated.
- VNPay callbacks use booking-first locking, reject expired/cancelled bookings and duplicate payment across attempts, preserve completed/refunded states on replay, and cannot revive cancelled inventory. A real late gateway settlement still needs manual reconciliation; the callback rejects it instead of confirming unavailable rooms.
- Search filters availability, effective nightly prices and room type in MySQL, orders by actual stay average price or newest, then paginates 12 rooms per page. Price precedence is override, highest-priority seasonal price (newest ID breaks ties), base price. Stays/searches are limited to 90 nights.
- Hosts can register/edit their own homestays and submit PDF/JPG/PNG verification documents (4 MB max). Documents stay on the private local disk, downloadable only by the owner/admin. Submission resets the homestay to pending; admin reviews record reviewer, time, status and rejection note. New approval must go through verification, while legacy approved homestays remain editable.
- Guest experience history/cancellation and host attendee/completion pages are available. Cancellation releases capacity exactly once; paid cancellation requires 24 hours before start and records a simulated full refund. Completion requires the owner, a paid booking and elapsed experience end time.
- Guest/seller order cancellation before packing returns product stock and records simulated refunds. Order transitions lock/re-read status. Each new order is limited to one seller; duplicate product IDs are rejected. Packing/delivery requires payment. Product loyalty is awarded once at delivery, room loyalty once at payment; refunds reverse earned points.
- Admin analytics has two interactive Vue 3 charts, existing accessible tables, Laravel analytics fallback and streamed CSV export with server-side date filters and spreadsheet formula escaping.
- Vue 3.5.22 runtime and MIT license are served locally under `backend/public/js/vendor/`. Render functions avoid runtime template compilation and preserve CSP without `unsafe-eval`. Vite remains optional for these charts; Node/npm is not needed to run them.

Verification, file inventory, commands and remaining manual checks: `docs/COMPLETED_FLOWS_2026-10-18.md`.

## 17. Accounts, cart, redemption and Render preparation — 2026-10-18

This section supersedes the earlier blanket statement that every room refund is simulated. `mock_test` refunds remain ledger simulations; VNPay room payments now use the actual **sandbox** refund/query API, require a signed matching response and track unknown/processing states without blind retries. Production-money gateways remain unsupported.

- Account profile and password-change pages require the current password. New passwords are hashed. A session password fingerprint and fresh account-status/role check invalidate old password sessions and enforce locked accounts immediately across all web routes.
- Password recovery uses Laravel's hashed, single-use 60-minute reset tokens, rate limits, uniform messages for unknown/locked emails, and URLs from configured APP_URL. A reset/update revokes old tokens and database sessions; fingerprints also cover file sessions. Local mode without SMTP uses private logs. Added an HTTP Resend mail transport for hosts such as Render Free that block SMTP; sending real mail still needs provider credentials/sender verification.
- Admin can create host/seller/guest/admin accounts, update profiles, lock/unlock and change roles. Self-lock/demotion and removal of the last active administrator are guarded. Resource owners cannot change role before handing over their business data. Password hashes never appear in audit before/after payloads.
- Session cart supports add/update/remove, max 20 distinct products, current server prices, seller grouping and all-or-nothing stock consumption. Checkout UUIDs stored in `cart_checkouts` prevent duplicate orders/point debits after repeat submissions. Cart mutations use session locks and checkout locks the user and product rows.
- Product checkout can redeem points at 1 point = 1,000 VND, capped at 50% of each seller order. Used points are deducted atomically and restored upon cancellation. Earned points are based on discounted cash amount at delivery. Refunds reverse the net ledger for that reference; if previously earned points have been spent elsewhere, spendable balance is clamped to zero until future earnings cover the reversal.
- Schema extensions are in the two `2026_10_18` migrations: password reset support, cart checkout tracking, product order points/discount fields and payment gateway/refund tracking. The canonical SQL remains the baseline followed by Laravel migrations.
- VNPay has a dedicated IPN endpoint with protocol JSON responses, stores original create time/transaction number, and supports signed query/refund requests restricted to the sandbox endpoint. Late successful settlement is recorded for refund without reviving inventory. Admin can reconcile pending refunds and missed payment notifications. A timeout leaves an unknown refund state for reconciliation; local status is never marked refunded from an unverified response.
- Actual sandbox probe with the current local merchant returned **Website này chưa được phê duyệt**. Merchant approval is required before end-to-end external settlement/refund can be proved.
- Local backup/isolated restore was executed successfully using installed MySQL 8 client tools (the older XAMPP MariaDB client cannot load caching_sha2_password). Temporary restore database counts matched the source; production data was not restored over or deleted.
- Windows task `DT07-Homestay-Scheduler` is registered to run every minute while the user is logged in; task result 0 was observed. Tokens are cleaned daily. Docker Engine could not start, so container build is not claimed tested.
- Added Render Docker image, nginx/PHP-FPM/supervisor config and a two-service Free Blueprint. Empty-schema initialization and repeat startup were checked on a disposable database, including both availability triggers. Foundation import strips database-level DROP/CREATE/USE commands and refuses a partially occupied database.
- Render deployment remains pending account connection and external MySQL credentials. Free services sleep, do not persist uploads and block common SMTP ports. A paid persistent disk or another persistent storage solution is needed for durable uploads; an always-running service is needed for scheduler execution when idle. No paid service was created, no public deployment URL is claimed.

Deployment instructions and external prerequisites: `docs/RENDER_DEPLOYMENT.md`.

## 18. Cash and VNPay only — 2026-10-18

This section supersedes every earlier description of mock/test payment and automatic simulated refund behavior. The user explicitly requested removal of simulated payments.

- Removed all three `/payments/mock` web endpoints, their request validator and mock payment services. There is no guest endpoint that marks a payment paid based on a submitted success/failure value. Existing historical transactions are preserved.
- Checkout for rooms, products and experiences offers cash and VNPay. Unavailable VNPay is disabled with a short message. `VNPAY_ENABLED` defaults to false and is false locally because the merchant was not approved. Enabling it permits the existing room VNPay initiation; signed callbacks/IPN still process outstanding transactions when initiation is disabled. Product/experience VNPay remains unavailable.
- Cash selection is a guest-owned POST at the same checkout path ending `/payments/cash`. It creates one idempotent `gateway=cash`, `status=initiated` payment with no paid timestamp and no earned points. The amount comes from the locked booking/order, never from the client.
- Room cash selection converts held inventory to sold and confirms the reservation inside the booking/inventory transaction. It clears the 15-minute hold; payment remains outstanding until the host receives cash. Product/experience cash selection confirms the pending reservation/order.
- Only the owning host (rooms/experiences) or owning seller (products) can confirm actual cash receipt. POST `/chu-homestay/bookings/{booking}/cash/received`, `/chu-homestay/experience-bookings/{experienceBooking}/cash/received`, or `/nguoi-ban/orders/{order}/cash/received` requires `confirmed=1`. It locks the parent and payment, rejects repeats and any other paid attempt, then records the paid timestamp, audit actor and customer notification. Room points are earned on collection; product points require both delivery and collection, in either order.
- Cash-on-delivery orders may be packed/delivered with an outstanding selected cash payment. Delivery alone does not mark paid or award points.
- Cancellation releases inventory/capacity and restores applicable redeemed points, but does not automatically mark a paid transaction refunded. Owners confirm physical cash returns through matching `/cash/refunded` routes with `confirmed=1`. Guests cannot confirm cash refunds. The existing room VNPay refund API remains verified by gateway response. Legacy non-VNPay payments are not automatically refunded.
- No schema reset or migration is needed: all payment tables already support the cash gateway and initiated/paid/refunded statuses.

Current operation and validation: `docs/CASH_PAYMENTS.md`.

## 19. Docker deployment hardening — 2026-10-23

- Docker Compose now builds immutable Laravel/Nginx/FastAPI images rather than bind-mounting the repository over the built Laravel image.
- Container service discovery explicitly uses `mysql` and `python`; credentials are supplied through an ignored `.env.docker` based on `.env.docker.example`.
- A one-shot `init` service safely prepares an empty schema, runs migrations and creates the first administrator before the app and scheduler start.
- MySQL data and Laravel uploaded/runtime storage use named volumes. Nginx serves public assets from its image and uploaded public files from the shared storage volume.
- The base Compose port remains loopback-only. `docker-compose.https.yml` adds public Caddy HTTPS for a DNS-backed VPS deployment.
- The FastAPI database configuration accepts discrete Docker database variables, avoiding URL parsing failures when passwords contain reserved URL characters; the existing `DT07_DB_URL` remains supported for Render and local use.

## 20. VNPay sandbox merchant refresh — 2026-10-24

- Local ignored `backend/.env` now enables the VNPay sandbox merchant `BDU3W4CJ`; the checksum secret remains environment-only and is not committed to source or documentation.
- A signed sandbox checkout probe returned HTTP 200 with checkout markers and no terminal-approval error.
- Room booking remains `holding` until a valid signed VNPay result with matching amount reports both response and transaction status `00`; only then is it moved atomically to `confirmed`. Failed, late, duplicate, cancelled and expired callback paths retain the existing inventory and reconciliation protections.
- End-to-end browser payment with a VNPay sandbox test card and independently delivered IPN still requires manual verification.

## 21. Render transactional email completion — 2026-10-24

- The Render Blueprint uses the existing HTTPS-based Resend transport, enables transactional notifications and keeps the API key/sender address as dashboard-provided secrets.
- Room booking lifecycle email now reaches both the guest and owning host for creation, confirmation, cancellation, hold expiry, refund and completion.
- VNPay paid/failed/late-settlement notifications now reach both sides. Replayed failed callbacks do not resend the same payment-result email; late settlement warns that inventory was not restored and reconciliation is required.
- Cash selection, receipt and cash-refund notifications include the booking reference and notify both relevant parties for room bookings.

## 22. Gemini grounded room chatbot — 2026-10-24

- A responsive floating chatbot recommends real rooms from the existing MySQL catalogue using party size, location, optional stay dates, nightly budget and free-text preferences.
- Laravel enforces approved/active rooms, capacity, date inventory and effective price before Gemini ranks candidates. Model output is treated as untrusted: unknown/duplicate room IDs are discarded and room details/links always come from Eloquent.
- Gemini uses a server-only API key, short timeout, low-temperature JSON output, five-minute cache and per-IP throttling. Database-ranked fallback remains available when the provider is disabled, unavailable or over quota.
- Render Blueprint declares Gemini configuration with the API key at `sync: false`; current configured model is `gemini-3.5-flash` because a live provider probe accepted the supplied key and completed structured generation on that model.
- Implementation and manual verification steps: `docs/GEMINI_ROOM_CHATBOT.md`.
