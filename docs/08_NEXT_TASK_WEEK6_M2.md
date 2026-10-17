# NEXT_TASK_WEEK6_M2.md — ĐT-07

## Read first
Read `AGENTS.md`, `docs/PROJECT_CONTEXT.md`, the existing Week 4 schema/OpenAPI, current `docs/CODE_STATUS.md`, and inspect the repository before changing files.

Repository currently used by the student: https://github.com/huyentrang-blip223/datphong

## Current verified baseline from M1
- Runtime app is Laravel 9. Do **not** upgrade framework in this task.
- Database session storage is enabled and the `sessions` migration was run separately.
- Week 4 schema is already loaded separately; Laravel starter migrations may remain pending. Do not run the full migration stack casually.
- Login/logout works.
- M1 RBAC smoke tests: 5 passed.
- Full suite at M1 close: 7 passed.
- Homestay CRUD is the M1 managed entity.

## Week 6 / M2 goal
Implement the first major tourism business flow so the project can demonstrate:
1. room management,
2. room availability by date,
3. seasonal/override pricing,
4. homestay/room search and filtering,
5. successful booking,
6. anti-double-booking concurrency control,
7. use of the prepared tourism dataset (>=300 records; project dataset target is 3,850 main tourism records).

## Fixed data model decisions
Do not redesign these unless an existing code/schema mismatch requires a documented compatibility adapter:
- `homestays` and `rooms` are separate.
- `room_availability` is inventory by date with `units_total`, `units_held`, `units_sold`, optional `price_override`.
- `seasonal_prices` stores date-range pricing.
- `bookings` stores the reservation.
- `booking_status_logs` records state transitions.

## Required implementation
### A. Room management
- Add/read/update rooms owned by the current host (or admin).
- Enforce host ownership server-side.
- Validate capacity, base price, active/status fields.

### B. Availability and pricing
- Read availability for a room across a date range.
- Compute available units as `units_total - units_held - units_sold`.
- Price precedence for each night: `price_override` -> matching seasonal price -> room base price.
- Keep check-out exclusive when calculating nights.

### C. Search
- Search/filter by location, check-in, check-out, guests, and optional price range.
- Only return rooms that have sufficient capacity and availability for every night in the stay.
- Preserve filters when paginating if pagination exists.

### D. BookingService
- Put booking business rules in a Service, not in controllers.
- Start a DB transaction.
- Lock all relevant `room_availability` rows with row-level locking in a deterministic date order.
- Re-check availability after locking.
- If any date is unavailable, rollback and return a business conflict (HTTP 409 for API).
- If available, update held/sold counters according to the current booking state design, create the booking, write `booking_status_logs`, and commit.
- Do not produce HTTP 500 for normal availability conflict.

### E. M2 tests
Create a focused feature test class, e.g. `M2BookingFlowTest`, covering at minimum:
1. guest can search an available room for a valid date range,
2. capacity/date validation rejects invalid requests,
3. price calculation uses override/seasonal/base fallback correctly,
4. authenticated guest can create a valid booking,
5. booking updates expected availability counters,
6. insufficient availability is rejected,
7. a host cannot mutate another host's room,
8. last-unit conflict does not create two successful bookings; only one wins,
9. booking status log is written,
10. regression: all M1 tests still pass.

If true parallel HTTP execution is awkward in the local test environment, implement the concurrency proof at the service/transaction level in the most faithful way available and explain the limitation. Do not fake a parallel test.

## Data proof
Use the existing prepared dataset/schema when compatible. Report actual counts after loading/using the data. Do not claim 3,850 records are loaded in the runnable DB unless you actually verify it. Minimum official threshold for M2 evidence is 300 tourism records.

## Do not do in this task
- Do not upgrade Laravel.
- Do not replace the 21-table design with a new schema.
- Do not run all starter migrations over the existing Week 4 schema without checking conflicts.
- Do not implement the Python recommendation module yet.
- Do not fabricate screenshots, test passes, record counts, Git commits, or benchmark numbers.

## Finish by updating `docs/CODE_STATUS.md`
Record:
- files changed,
- DB changes,
- routes/endpoints added,
- exact commands run,
- actual record counts,
- focused M2 test result,
- full-suite result,
- concurrency-test method/result,
- known issues,
- screenshot list to capture,
- Git evidence to capture.
