# Test Round 1 Week 8 Summary

Designed cases: 60.

| Status | Count |
|---|---:|
| PASS | 51 |
| FAILED | 0 |
| BLOCKED | 0 |
| NOT EXECUTED | 9 |

Module counts follow the required allocation: AUTH 9, PERM 7, CATL 10, BOOK 10, PAY 6, ORDR 6, ADMN 6, PYMD 3, VALD 3.

Evidence used for PASS cases:
- `php artisan test --filter=M1AuthRoleSmokeTest` -> 6 passed.
- `php artisan test --filter=M2BookingFlowTest` -> 9 passed.
- `php artisan test --filter=M3PythonIntegrationTest` -> 6 passed.
- `php artisan test --filter=Week8` -> 13 passed.
- `python -m pytest -q` -> 5 passed.
- Source review for CSRF, payment mock fields, session config, and query paths.

Not executed cases require a real browser or additional direct API/manual checks. They were intentionally not marked PASS.
