# NEXT_TASK_WEEK9_CODE_FREEZE_DT07.md

## CSE703073 — ĐT-07 — Project Week 9 / Tuần học kỳ 14

**Mục tiêu ưu tiên của task này:** hoàn thiện CODE trước, đưa hệ thống tới trạng thái **code freeze / deployment-ready**, sau đó mới làm hồ sơ báo cáo.

**Đề tài:** Sàn lưu trú cộng đồng homestay gắn với sản phẩm địa phương  
**Repository:** `huyentrang-blip223/datphong`

> Lưu ý: tại thời điểm tạo task, nhánh `main` trên GitHub vẫn đang có commit mới nhất:
> `682aed4` — `Implement M3 Python recommendations and analytics`.
> Nếu Week 8 chưa được implement/push, PHẢI hoàn thiện các phần code-critical của Week 8 trước khi bắt đầu go-live Week 9.

---

# 0. NGUYÊN TẮC LÀM VIỆC

1. Không nâng Laravel.
2. Giữ runtime thực tế:
   - Laravel 9
   - MySQL
   - Blade/CSS/JS hiện có
   - FastAPI/Python
3. Không phá M1/M2/M3.
4. Không commit `.env`, token, password, API key.
5. Không ghi "PASS", "GO-LIVE", "HTTPS OK", "BACKUP OK", "RESTORE OK" nếu chưa chạy thật.
6. Nếu không có credential cloud/hosting, chỉ chuẩn bị deployment-ready package và ghi `NOT DEPLOYED`; không bịa URL.
7. Không dùng dữ liệu cá nhân thật.
8. Payment chỉ ở chế độ test/mock/sandbox; không lưu dữ liệu thẻ thật.
9. Mọi sửa lỗi phải có regression test khi hợp lý.
10. Code freeze chỉ được chấp nhận khi không còn blocker Critical/High ảnh hưởng luồng chính.

---

# 1. ĐỌC REPO VÀ XÁC ĐỊNH BASELINE

Đọc:

- `AGENTS.md`
- `docs/PROJECT_CONTEXT.md`
- `docs/CODE_STATUS.md`
- `docs/NEXT_TASK_WEEK8_FULL_DT07.md` nếu có
- `docs/WEEK8_FUNCTION_GAP_MATRIX.md` nếu có
- `docs/BUG_LOG_WEEK8.csv` nếu có
- `docs/TEST_ROUND1_WEEK8.csv` nếu có
- `database/schema_DT07.sql`
- `backend/routes/web.php`
- `backend/composer.json`
- `backend/.env.example`
- toàn bộ `backend/app`, `backend/resources/views`, `backend/tests`
- toàn bộ `python-service`

Chạy:

```powershell
git status --short
git branch --all
git log --oneline --decorate -15
```

Tạo snapshot kỹ thuật:

```text
docs/WEEK9_BASELINE_AUDIT.md
```

Ghi rõ:
- commit baseline;
- Week 8 đã có/chưa có;
- chức năng thiếu;
- test hiện tại;
- deployment files hiện có/chưa có;
- bug Critical/High đang mở;
- các secret/config cần xử lý trước production.

---

# 2. PHASE A — NẾU WEEK 8 CHƯA XONG, HOÀN THIỆN CODE TRƯỚC

Không được nhảy thẳng deploy nếu các phần code chính vẫn thiếu.

## 2.1. UI bắt buộc

Phải có:

- 02 theme thật;
- theme switch;
- theme persist sau reload;
- responsive:
  - 390 x 844
  - 768 x 1024
  - 1440 x 900
- menu mobile usable;
- table/form/cards không tràn;
- focus state nhìn thấy;
- label cho form;
- footer học thuật.

Nếu Week 8 đã code đủ, chỉ rà regression.

## 2.2. Chức năng còn thiếu

Inspect schema rồi hoàn thiện ưu tiên:

- host availability/calendar;
- seasonal pricing;
- seller local product CRUD + ownership;
- product order flow nếu schema đủ;
- payment test/mock nếu schema đủ;
- booking/order history;
- review hợp lệ;
- notifications/loyalty nếu không ảnh hưởng tiến độ.

Nếu schema không hỗ trợ một flow an toàn, ghi blocker; không bịa.

## 2.3. Week 8 bug fixes

Nếu có `BUG_LOG_WEEK8.csv`:
- fix toàn bộ Critical;
- ưu tiên fix High;
- retest bug đã sửa;
- cập nhật test tự động.

---

# 3. PHASE B — CODE FREEZE CHO LUỒNG NGHIỆP VỤ

Sau khi Week 8 đủ, rà toàn hệ thống theo actor.

## 3.1. Guest

Phải kiểm tra được:

- đăng nhập/đăng xuất;
- tìm kiếm homestay/phòng;
- lọc ngày/khách/giá;
- xem sản phẩm địa phương;
- recommendation Python;
- booking accommodation;
- conflict 409 khi hết phòng;
- xem trải nghiệm;
- booking experience;
- lịch sử booking/order nếu có;
- review nếu đủ điều kiện;
- không truy cập admin/host/seller route.

## 3.2. Host

Phải kiểm tra:

- dashboard;
- ownership homestay;
- room CRUD;
- availability/calendar;
- seasonal pricing;
- không sửa dữ liệu Host khác;
- xem booking liên quan nếu đã triển khai.

## 3.3. Seller

Phải kiểm tra:

- seller dashboard;
- product CRUD;
- ownership;
- stock;
- order scope nếu module order đã có.

## 3.4. Admin

Phải kiểm tra:

- dashboard;
- homestay management;
- analytics Python;
- user/system counts;
- không crash khi Python down.

---

# 4. PRODUCTION-SAFE CONFIG

Cập nhật `.env.example` và config, KHÔNG sửa secret thật vào Git.

Production values cần hỗ trợ:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com

LOG_CHANNEL=stack
LOG_LEVEL=warning

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

PY_SERVICE_URL=http://python:8001
PY_SERVICE_TOKEN=
PY_SERVICE_TIMEOUT=3
```

Nếu dùng Docker Compose, host Python có thể là `python`.

Không hard-code DB password.

Tạo:

```text
backend/.env.production.example
python-service/.env.production.example
```

với placeholder an toàn.

---

# 5. HEALTH CHECK HỢP NHẤT

Laravel hiện cần một endpoint health tổng.

Tạo:

```text
GET /healthz
```

JSON dự kiến:

```json
{
  "application": "ok",
  "database": "ok",
  "python": "ok",
  "timestamp": "..."
}
```

Yêu cầu:

- DB lỗi -> HTTP 503;
- Python down không làm app crash;
- nếu Python là dependency không-critical, có thể trả app 200 với `python: unavailable`, nhưng phải ghi rõ design;
- không lộ exception message, stack trace, secret, đường dẫn máy;
- test health endpoint.

FastAPI `/healthz` vẫn giữ.

---

# 6. ERROR HANDLING PRODUCTION

Tạo/kiểm tra:

- 403 friendly;
- 404 friendly;
- 419 friendly;
- 422 JSON/form validation rõ;
- 500 generic, không lộ stack trace;
- production `APP_DEBUG=false`.

Không log password/token.

Test route sai và ID không tồn tại.

---

# 7. SECURITY HEADERS

Nếu deploy qua Nginx, cấu hình tối thiểu:

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `Referrer-Policy`
- `Content-Security-Policy` phù hợp app hiện tại

Chỉ bật HSTS trên HTTPS production thật.

Không cấu hình CSP làm hỏng Blade assets.

Tạo test hoặc script curl kiểm tra headers.

---

# 8. DOCKER COMPOSE — BẮT BUỘC CHUẨN BỊ

Mục tiêu: một lệnh chạy toàn stack.

Cấu trúc gợi ý:

```text
docker-compose.yml
docker/
  nginx/
    default.conf
  php/
    Dockerfile
  python/
    Dockerfile
scripts/
  docker-up.ps1
  docker-down.ps1
  docker-doctor.ps1
```

Services tối thiểu:

- `nginx`
- `app` / PHP-FPM
- `mysql`
- `python`

Có thể có:
- scheduler/queue nếu app thực sự dùng.

Yêu cầu:

- MySQL persistent volume;
- public uploads persistent nếu app có upload;
- app không chứa `.env` trong image;
- Python token qua environment;
- healthcheck cho app/db/python nếu khả thi;
- dependency startup hợp lý;
- `docker compose up -d --build` chạy được;
- `docker compose ps` rõ trạng thái;
- không expose MySQL ra Internet trong production compose.

Nếu Windows local không có Docker Desktop, vẫn tạo package nhưng ghi `NOT EXECUTED`.

---

# 9. NGINX / HTTPS READY

Tạo config Nginx production-ready:

- front controller Laravel;
- deny dotfiles/secret files;
- static caching cơ bản;
- request size hợp lý;
- proxy/fastcgi headers chuẩn;
- support HTTPS reverse proxy environment.

Không tự sinh chứng chỉ giả để tuyên bố HTTPS.

Nếu user chưa chọn hosting/domain:
- chuẩn bị `docker/nginx/default.conf`;
- tạo `docs/DEPLOYMENT_OPTIONS.md`;
- không deploy.

Nếu có domain/credentials:
- triển khai thật;
- dùng HTTPS hợp lệ;
- ghi URL.

---

# 10. BACKUP + RESTORE DATABASE

Tạo scripts:

```text
scripts/backup-db.ps1
scripts/restore-db-test.ps1
```

Yêu cầu:

- backup dùng biến môi trường;
- không ghi password trong file;
- tên file có timestamp;
- thư mục backup nằm trong `.gitignore`;
- restore test vào DB tạm;
- sau restore chạy COUNT các bảng chính;
- xóa DB test sau khi kiểm chứng.

Chỉ ghi `RESTORE PASS` nếu thực sự chạy restore test.

---

# 11. LOGGING / AUDIT

Rà logging:

- login failure quan trọng;
- permission denial nếu phù hợp;
- booking conflict;
- order/payment conflict;
- Python unavailable;
- deployment health failures.

Không log:
- password;
- session cookie;
- CSRF token;
- Python service token;
- DB password.

Đảm bảo `storage/logs` không commit.

---

# 12. RATE LIMIT LOGIN

Rà login brute-force protection.

Nếu chưa có, bổ sung rate limiting hợp lý cho login.

Mục tiêu ví dụ:
- giới hạn số lần thử trong khoảng thời gian;
- thông báo thân thiện;
- test throttling.

Không khóa vĩnh viễn user demo.

---

# 13. FILE UPLOAD — CHỈ NẾU APP CÓ UPLOAD

Nếu app có upload ảnh:

- MIME whitelist;
- size limit;
- random filename;
- không cho `.php`, script executable;
- persistent storage;
- kiểm tra file vẫn tồn tại sau restart Docker.

Nếu app chưa có upload và không phải flow bắt buộc hiện tại, không thêm chỉ để lấy điểm.

---

# 14. ROUND 2 — AUTOMATED REGRESSION

Chạy lại toàn bộ:

```powershell
cd backend

C:\xampp\php\php.exe artisan test --filter=M1AuthRoleSmokeTest
C:\xampp\php\php.exe artisan test --filter=M2BookingFlowTest
C:\xampp\php\php.exe artisan test --filter=M3PythonIntegrationTest
C:\xampp\php\php.exe artisan test --filter=Week8
C:\xampp\php\php.exe artisan test --filter=Week9
C:\xampp\php\php.exe artisan test
```

Python:

```powershell
cd python-service
python -m pytest -q
```

Nếu filter `Week8` không tồn tại, chạy tên test thực tế và ghi rõ.

Round 2 phải retest mọi bug đã fix ở Round 1.

---

# 15. ROUND 2 — 60 FUNCTIONAL CASES

Nếu Week 8 đã tạo:

```text
docs/TEST_ROUND1_WEEK8.csv
```

thì tạo:

```text
docs/TEST_ROUND2_WEEK9.csv
```

Giữ cùng ID test để so sánh vòng 1 và vòng 2.

Không tự ghi tất cả PASS.

Trường bắt buộc:

1. Mã ca kiểm thử
2. Mô-đun / chức năng
3. Ca sử dụng liên quan
4. Mục tiêu kiểm thử
5. Tiền điều kiện
6. Các bước thực hiện
7. Dữ liệu đầu vào
8. Kết quả mong đợi
9. Kết quả thực tế
10. Trạng thái
11. Mức nghiêm trọng
12. Người thực hiện
13. Ngày thực hiện / ghi chú tách hợp lý theo form đang dùng

Nếu file Week8 đang dùng schema 13 field khác, giữ nhất quán nhưng phải map được về 13 trường chính thức.

---

# 16. BROWSER + RESPONSIVE RETEST

Rerun browser matrix sau khi fix:

- Chromium
- Firefox
- WebKit hoặc Edge

Viewport:
- 390
- 768
- 1440

Tập trung:
- login;
- search;
- booking;
- products;
- experiences;
- host management;
- seller management;
- admin dashboard.

Tạo:

```text
docs/BROWSER_MATRIX_WEEK9.csv
```

Chỉ ghi PASS nếu chạy thật.

---

# 17. PERFORMANCE RETEST

Nếu Week8 đã có Lighthouse baseline:
- chạy lại 05 trang;
- so sánh before/after.

Nếu Week8 chưa đo:
- đo lần đầu Week9;
- không bịa "before".

Trang:
1. home
2. search
3. product detail
4. experience detail
5. admin/host dashboard

Tạo:

```text
docs/PERFORMANCE_WEEK9.csv
```

---

# 18. OWASP RETEST

Rerun các điểm Critical/High từ review Week8.

Bắt buộc kiểm:
- direct URL role escalation;
- ownership/IDOR;
- SQL injection input;
- XSS input;
- CSRF;
- login brute force;
- `.env` inaccessible production;
- APP_DEBUG false;
- Python token protected;
- dependency audit.

Commands nếu phù hợp:

```powershell
composer audit
python -m pip list --outdated
```

Không tự nâng dependency major nếu làm vỡ app.

---

# 19. GO-LIVE

## 19.1. Nếu có hosting/domain/credentials

Deploy thật.

Yêu cầu:
- public URL;
- HTTPS valid;
- stable URL;
- app, DB, Python hoạt động;
- seed demo/test accounts;
- `APP_DEBUG=false`;
- session Secure;
- public footer;
- health endpoint.

Không ghi secret vào repo.

## 19.2. Nếu chưa có credentials

Không dừng việc.

Hoàn thiện:
- Docker Compose;
- Nginx config;
- production env examples;
- deployment scripts;
- backup/restore;
- health;
- runbook.

Ghi:
`GO-LIVE: BLOCKED — hosting/domain credentials not provided`.

---

# 20. 10 KIỂM TRA SAU TRIỂN KHAI

Nếu đã go-live thật, kiểm tra và ghi:

```text
docs/POST_DEPLOY_CHECK_WEEK9.md
```

Tối thiểu:

1. Public access bằng mạng ngoài.
2. HTTPS valid.
3. HTTP -> HTTPS.
4. Security headers.
5. 404/sai tham số không lộ stack/path.
6. Test accounts đăng nhập đúng role.
7. Dữ liệu production/demo >=300.
8. Persistent uploaded files nếu feature upload tồn tại.
9. Python feature hoạt động, không 500.
10. Footer đúng thông tin học thuật.

Nếu mục nào không áp dụng, ghi N/A và lý do.

---

# 21. UPTIME / MONITORING

Nếu go-live:

- `/healthz` public;
- chuẩn bị check định kỳ;
- nếu có thể dùng external uptime monitor;
- không cần trả tiền.

Tạo:

```text
docs/MONITORING_RUNBOOK.md
```

Ghi cách:
- kiểm tra health;
- đọc log;
- restart service;
- backup;
- rollback.

---

# 22. SURVEY / SUS — KHÔNG BỊA DỮ LIỆU

Tuần 9 có yêu cầu dữ liệu khảo sát/đánh giá.

Code-first task chỉ cần chuẩn bị form/template:

```text
docs/SUS_SURVEY_TEMPLATE.csv
docs/USABILITY_TEST_SCRIPT.md
```

Không tự tạo câu trả lời giả.

Chuẩn bị:
- tối thiểu 05 người dùng thực cho giai đoạn đánh giá;
- 10 câu SUS;
- task dùng thử rõ;
- cột ghi phát hiện;
- cách tính điểm.

Không điền điểm bình quân trước khi có người dùng thật.

---

# 23. VIDEO DEMO READY

Chưa cần dựng video trong task code-first, nhưng app phải sẵn sàng quay 08–12 phút.

Tạo:

```text
docs/DEMO_FLOW_WEEK9.md
```

Flow đề xuất:
1. home;
2. login guest;
3. search;
4. booking;
5. local product + Python recommendation;
6. experience booking;
7. host room/availability/pricing;
8. seller product;
9. admin analytics;
10. theme/mobile;
11. health/go-live.

Không tạo video giả.

---

# 24. README PRODUCTION READY

Cập nhật `README.md` để phản ánh code thật:

- Laravel 9, không ghi Laravel 11;
- MySQL;
- Blade;
- FastAPI/Python;
- install local;
- Docker Compose;
- test commands;
- demo data source;
- health check;
- deployment;
- known limitations.

Không để password thật trong README.

---

# 25. SECRET SCAN / REPO HYGIENE

Trước code freeze:

```powershell
git status --short
git grep -n "m3-local-token"
git grep -n "DB_PASSWORD="
git grep -n "PY_SERVICE_TOKEN="
git log --all --name-only --pretty=format: | Select-String -Pattern "\.env$"
```

Rà:
- `.env`
- `.env.production`
- SQL dump chứa credential
- private key
- token
- cookie
- log chứa secret

Nếu secret từng bị commit:
- báo user;
- rotate secret;
- không giả vờ chỉ xóa file là đủ.

---

# 26. TEST ACCOUNTS

Chuẩn bị deterministic demo accounts cho các role mà app thực sự hỗ trợ:

- admin
- host
- seller
- guest

Không commit password production thật.

Có thể seed password demo riêng cho môi trường demo.

Tạo:
```text
docs/TEST_ACCOUNTS_TEMPLATE.md
```

Chỉ điền password khi user quyết định môi trường demo/nộp.

---

# 27. CODE FREEZE CHECKLIST

Chỉ báo `CODE FREEZE READY` nếu:

- [ ] Week8 code-critical work đã xong
- [ ] 2 themes
- [ ] responsive 390/768/1440
- [ ] guest core flow chạy
- [ ] host core flow chạy
- [ ] seller core flow chạy hoặc blocker có căn cứ schema
- [ ] admin core flow chạy
- [ ] accommodation booking chống overbook
- [ ] experience capacity conflict xử lý
- [ ] Python recommendation + analytics
- [ ] Python fallback
- [ ] runtime data >=300
- [ ] production env examples
- [ ] Laravel `/healthz`
- [ ] friendly error pages
- [ ] security headers config
- [ ] Docker Compose package
- [ ] backup script
- [ ] restore test script
- [ ] login rate limit
- [ ] Round2 regression tests
- [ ] no Critical bug open
- [ ] no secret tracked
- [ ] README khớp runtime
- [ ] deploy package sẵn sàng

Go-live URL không phải điều kiện code freeze nếu user chưa cung cấp hosting credential, nhưng phải là blocker duy nhất thuộc môi trường triển khai chứ không phải blocker code.

---

# 28. CẬP NHẬT `docs/PROJECT_CONTEXT.md`

Thêm:

```text
## Week 9 / Code Freeze status
```

Ghi:
- Week8 carried fixes;
- final features;
- deployment architecture;
- health endpoint;
- backup/restore;
- security;
- test round 2;
- go-live status;
- known blockers.

---

# 29. CẬP NHẬT `docs/CODE_STATUS.md`

Phải có:

## Current milestone
Week 9 / Code Freeze & Deployment

## Baseline
SHA thật.

## Code freeze
READY / NOT READY

## Features complete
Chi tiết theo actor.

## Week8 carry-over
Đã hoàn tất gì.

## Deployment files
Liệt kê.

## Docker
- build:
- compose up:
- health:
- status:

## Backup/restore
- backup:
- restore:
- counts:

## Security
- debug:
- secure cookie:
- rate limit:
- headers:
- secret scan:
- audit:

## Round 2 tests
- Python:
- M1:
- M2:
- M3:
- Week8:
- Week9:
- full suite:

## Browser matrix
Kết quả thật.

## Performance
Kết quả thật hoặc NOT EXECUTED.

## Go-live
- URL:
- HTTPS:
- POST DEPLOY:
hoặc BLOCKED.

## Known issues
Không che lỗi.

## Screenshots user must capture
Danh sách chính xác.

## Git
- status
- branch
- commit SHA chỉ khi commit thật.

---

# 30. GIT / COMMIT

Không tự tạo commit nếu user chưa yêu cầu Codex commit/push.

Nếu được phép commit:

- không commit `.env`;
- dùng conventional commit, ví dụ:

```text
feat(week9): prepare production deployment and code freeze
fix(security): harden production runtime checks
test(round2): complete week9 regression suite
```

Sau commit:
```powershell
git status --short
git log --oneline --decorate -10
```

---

# 31. RESPONSE CUỐI CỦA CODEX

Trả đúng format:

```text
Week 9 code-first status: READY / PARTIALLY READY / BLOCKED

1. Baseline
- commit:
- branch:

2. Week8 carry-over completed
- ...

3. Final features
Guest:
Host:
Seller:
Admin:
Python:

4. Deployment readiness
- Docker:
- Nginx:
- production env:
- health:
- backup/restore:
- logging:
- security headers:

5. Tests actually run
- Python:
- M1:
- M2:
- M3:
- Week8:
- Week9:
- full suite:

6. Bug status
- Critical open:
- High open:
- Medium:
- Low:

7. Security
- APP_DEBUG:
- secure session:
- rate limit:
- secret scan:
- audits:

8. Go-live
- URL:
- HTTPS:
- public check:
or BLOCKED with reason

9. Round 2
- functional cases:
- browser matrix:
- performance:

10. Known issues
- ...

11. Exact screenshots user should capture
- ...

12. Git status
- ...
```

Không ghi READY nếu còn bug Critical/High làm hỏng core flow, Docker/deployment package chưa chạy được, hoặc full regression chưa chạy.
