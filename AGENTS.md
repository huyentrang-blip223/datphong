# AGENTS.md — CSE703073 ĐT-07

## Project
Course: CSE703073 — Lập trình ứng dụng web trong du lịch 2  
Topic: ĐT-07 — Sàn lưu trú cộng đồng homestay gắn với sản phẩm địa phương.

## Source of truth
Before coding, read:
1. `docs/PROJECT_CONTEXT.md`
2. `docs/NEXT_TASK.md`
3. Existing database schema / OpenAPI / tests in the repository.

Do not redesign the project from scratch unless the user explicitly asks.

## Fixed scope
The system is centered on community homestay accommodation. Local products and cultural experiences are differentiating features.

Core roles:
- admin
- guest
- host
- seller

Core modules:
- authentication and role-based authorization
- homestay verification
- rooms
- room availability by date
- seasonal pricing
- homestay search/filter
- booking with anti-double-booking logic
- local products and product orders
- cultural experiences and experience booking
- reviews linked to completed bookings
- loyalty points
- notifications
- audit logs

## Architecture
Preferred stack for this project:
- Laravel 11 backend
- MySQL 8.0
- Vue 3 + Vite frontend where needed
- Python/FastAPI module later in the project
- REST API prefix: `/api/v1`

Maintain separation of concerns:
- Controller: HTTP/request coordination only
- Service: business rules
- Model/Repository/Eloquent: data access
- Request classes: server-side validation
- Middleware/Policy: authorization

Do not place raw SQL directly inside controllers.

## Database constraints
The Week 4 model currently has 21 tables.
Important modeling decisions:
- `homestays` and `rooms` remain separate.
- `room_availability` stores inventory by date using `units_total`, `units_held`, `units_sold`, and optional `price_override`.
- `seasonal_prices` stores pricing ranges instead of overwriting room base price.
- M:N relations include `room_amenities`, `homestay_products`, and `product_order_items`.
- `reviews` must only be associated with completed bookings.
- `booking_status_logs` and `audit_logs` provide traceability.
- Do not silently collapse products/experiences into room bookings.

## Booking rule
Prevent double booking at the server/database layer.
Use a database transaction and row-level locking when consuming availability.
On availability conflict, return HTTP 409 for API requests.

## Security
- Never store plaintext passwords.
- Use Laravel Hash (bcrypt or argon2id).
- Regenerate session after successful login.
- Invalidate session and regenerate CSRF token on logout.
- Authorization must be enforced server-side.
- Hiding UI controls is not sufficient.
- Enforce ownership where relevant, e.g. a host cannot edit another host's homestay.
- Validate all mutable requests on the server.
- Keep CSRF protection enabled for web forms.

## Week 5 / M1 target
M1 must demonstrate:
- framework skeleton runs
- login/logout works
- role-based authorization works
- common layout/navigation exists
- at least one managed entity has working basic CRUD

For M1, use `homestays` as the CRUD entity.

Expected proof:
- admin can access homestay administration
- host/seller/guest cannot access admin-only route
- unauthenticated user is redirected to login
- ownership checks deny cross-host editing
- create/update validation works
- passwords are hashed
- session/cookie configuration is inspectable

## Working rules
- Inspect the existing project before changing files.
- Reuse current schema, API contract, names, and business decisions.
- Do not invent runtime results, test passes, Git commits, URLs, screenshots, or MySQL benchmark numbers.
- If a test cannot be run, mark it as not executed and explain what remains.
- Run relevant tests/lint/syntax checks after changes.
- Keep changes small enough to review.
- Do not delete existing project artifacts unless explicitly requested.
- When you change architecture, schema, API routes, or business rules, update `docs/PROJECT_CONTEXT.md`.

## Response style
When finishing a coding task, report:
- files changed
- commands run
- tests/results
- anything still requiring manual local verification
