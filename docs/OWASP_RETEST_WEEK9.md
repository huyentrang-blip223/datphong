# OWASP Retest Week 9

| Area | Check | Result | Evidence |
|---|---|---|---|
| Direct URL role escalation | Role middleware and tests | PASS | M1 and Week8 permission tests |
| Ownership/IDOR | Host/seller/guest ownership | PASS | Week8 and Week9 tests |
| SQL injection input | Eloquent/query builder review | PASS | Source review; no raw SQL in controllers |
| XSS input | Blade escaping by default | PASS by source review | User content rendered with `{{ }}` |
| CSRF | Web middleware enabled | PASS | `VerifyCsrfToken` in web group |
| Login brute force | Login throttle | PASS | Week9 test returns 429 |
| `.env` inaccessible production | Nginx denies dotfiles/env | PREPARED | `docker/nginx/default.conf` |
| `APP_DEBUG=false` | Production env example | PREPARED | `backend/.env.production.example` |
| Python token protected | Env based token | PREPARED | No token value committed |
| Dependency audit | Composer audit | FAIL/HIGH BLOCKER | 16 advisories affecting 7 packages |

Code freeze cannot be marked READY while High dependency advisories remain unresolved or explicitly accepted by the project owner.
