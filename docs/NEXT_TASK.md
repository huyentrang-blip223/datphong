# NEXT_TASK.md — Codex task for Week 5 / M1

Inspect the existing repository first, then implement or complete M1 without replacing the Week 4 design.

## Goal
Produce a locally runnable Laravel 11 M1 that demonstrates authentication, server-side RBAC, common layout/navigation, and CRUD for `homestays`.

## Required implementation
1. Confirm DB connection to the existing MySQL schema.
2. Adapt the Laravel `User` model to the existing `users` table/fields.
3. Implement login and logout.
4. Regenerate session after successful login.
5. Add role middleware for `admin`, `host`, `seller`, `guest`.
6. Protect admin routes server-side.
7. Add host ownership checks/policy so Host A cannot edit Host B's homestay.
8. Implement admin CRUD for `homestays`:
   - index
   - create/store
   - edit/update
   - validation
9. Use common Blade layout/header/footer/navigation.
10. Keep routes and naming consistent with the Week 4 API/design docs.
11. Add automated smoke/feature tests for:
   - admin allowed
   - host denied admin route
   - unauthenticated redirected
   - seller denied host-only route
   - cross-owner edit denied

## Do not fake
Do not claim tests pass unless you actually run them.
Do not invent MySQL benchmark results, screenshots, Git commit IDs, URLs, or deployment state.

## Finish by reporting
- exact files changed
- setup commands
- test commands run
- pass/fail output
- manual steps the student must still do
