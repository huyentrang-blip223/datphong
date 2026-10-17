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
