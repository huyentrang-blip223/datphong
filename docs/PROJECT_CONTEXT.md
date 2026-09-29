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
