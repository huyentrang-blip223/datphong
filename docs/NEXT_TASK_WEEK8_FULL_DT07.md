# NEXT_TASK_WEEK8_FULL_DT07.md

## CSE703073 — ĐT-07 — Project Week 8 / Tuần học kỳ 13

**Đề tài:** Sàn lưu trú cộng đồng homestay gắn với sản phẩm địa phương  
**Repository:** `huyentrang-blip223/datphong`  
**Baseline:** commit M3 `682aed4` — `Implement M3 Python recommendations and analytics`  
**Runtime hiện tại:** Laravel 9 + MySQL + FastAPI/Python  
**Không nâng Laravel trong tuần này.**

## 0. Mục tiêu Tuần 8

Tuần 8 phải hoàn thành: chức năng còn thiếu; 02 theme giao diện; responsive 390/768/1440; kiểm thử chức năng vòng 1; danh mục 60 ca kiểm thử đủ 13 trường; sổ lỗi vòng 1; bản thảo Chương 3; regression M1/M2/M3; cập nhật `PROJECT_CONTEXT.md` và `CODE_STATUS.md` bằng kết quả chạy thật. Không bịa PASS, screenshot, Lighthouse, browser matrix, commit hoặc số liệu runtime.

## 1. Đọc source trước khi sửa

Đọc: `AGENTS.md`, `docs/PROJECT_CONTEXT.md`, `docs/CODE_STATUS.md`, `docs/CHUONG_1_M3_NHAP.md`, `docs/CHUONG_2_M3_NHAP.md`, `database/schema_DT07.sql`, `backend/routes/web.php`, `backend/resources/views/layouts/app.blade.php`, toàn bộ controller/service/model/test M1-M3 và `python-service/`.

Chạy trước:

```bash
git status --short
git log --oneline -10
C:\xampp\php\php.exe artisan test --filter=M1AuthRoleSmokeTest
C:\xampp\php\php.exe artisan test --filter=M2BookingFlowTest
C:\xampp\php\php.exe artisan test --filter=M3PythonIntegrationTest
C:\xampp\php\php.exe artisan test
python -m pytest -q
```

Không sửa/commit `.env`.

## 2. Lập gap matrix trước khi code

Tạo `docs/WEEK8_FUNCTION_GAP_MATRIX.md` với bảng: Nhóm | Chức năng | Trạng thái trước Tuần 8 | Route/file hiện có | Việc cần làm | Kết quả sau Tuần 8.

Rà tối thiểu: AUTH/session, RBAC/ownership, homestay, room, room availability/calendar, seasonal price, search/filter, booking, booking history/cancel, local products, seller CRUD, product order/cart, payment test mode, experiences, experience booking, reviews, notifications, loyalty, admin/reporting, Python recommender/analytics, audit/logging.

Không tự thêm bảng/cột không có trong schema nếu chưa giải thích.

## 3. Hoàn thiện chức năng còn thiếu

### 3.1 Host availability/calendar + seasonal pricing

Bổ sung UI/service/controller để host:
- chỉ thao tác room thuộc homestay của mình;
- xem availability theo khoảng ngày;
- cập nhật `units_total`, `units_held`, `price_override` đúng business rule;
- chặn `units_total < units_held + units_sold`;
- quản lý `seasonal_prices`;
- validate `start_date <= end_date`;
- giữ quy tắc giá `price_override -> seasonal -> base_price`;
- test Host A không sửa dữ liệu Host B.

### 3.2 Seller local product CRUD

Bổ sung seller dashboard/quản lý sản phẩm: index/create/store/edit/update, validation giá/tồn/status, seller chỉ sửa sản phẩm của mình, Seller A sửa Seller B -> 403. Recommendation Python phải tiếp tục hoạt động sau CRUD.

### 3.3 Product order / order state

Nếu schema `product_orders` + `product_order_items` hỗ trợ:
- guest tạo order;
- server tự tính tổng từ DB;
- kiểm tra stock;
- transaction khi tạo order;
- lưu items;
- guest xem order của mình;
- seller/admin chỉ xem/đổi trạng thái trong phạm vi hợp lệ;
- chặn state transition không hợp lệ.

Nếu schema không đủ, ghi blocker rõ, không giả chức năng.

### 3.4 Payment test mode

Nếu bảng `payments` hỗ trợ:
- chỉ dùng mock/test payment;
- không lưu thông tin thẻ thật;
- success/fail rõ;
- chống thanh toán hai lần;
- cập nhật trạng thái liên quan trong transaction;
- có test success/fail/duplicate.

### 3.5 Reviews

Bổ sung review với rule:
- chỉ user có giao dịch hợp lệ;
- chỉ giao dịch hoàn tất nếu schema/rule hỗ trợ;
- rating đúng biên;
- ownership chặt;
- đổi ID không tạo review ảo.

### 3.6 Booking history/cancel

Nếu schema hỗ trợ: guest xem booking của mình; không xem booking người khác; hủy theo state rule; rollback availability nếu nghiệp vụ yêu cầu.

### 3.7 Notifications/loyalty

Chỉ làm sau khi Must ở trên hoàn tất. Không tạo route giả.

## 4. Hai theme giao diện bắt buộc

Hiện layout mới có một theme. Triển khai hai theme thật, ví dụ `light` và `forest`/`dark`:
- dùng CSS custom properties;
- `data-theme` trên html/body;
- nút đổi theme;
- persist bằng `localStorage`;
- reload vẫn giữ;
- có thể theo `prefers-color-scheme` khi chưa chọn;
- áp dụng đồng bộ header/footer/cards/table/form/button/alert;
- tương phản chữ/nền đủ đọc;
- tách CSS/JS khỏi layout nếu hợp lý.

## 5. Responsive 390 / 768 / 1440

Kiểm tra ba viewport:
- `390x844`
- `768x1024`
- `1440x900`

Yêu cầu: không overflow ngang vô lý; menu mobile dùng được; form/card/table responsive; dashboard đọc được trên mobile; product/experience/search/seller/host forms dùng được; focus state nhìn thấy; input có label; ảnh responsive; text không quá nhỏ.

Tạo `docs/RESPONSIVE_THEME_WEEK8.md` ghi route nào đã kiểm tra ở từng viewport/theme.

## 6. Footer học thuật

Bổ sung footer dùng chung nếu chưa có:
- `CSE703073 – Lập trình ứng dụng web trong du lịch 2`;
- tên/mã nhóm nếu config có;
- ghi chú sản phẩm học thuật;
- dữ liệu mô phỏng/project-seed;
- phiên bản nếu có.

Không hard-code dữ liệu cá nhân chưa xác nhận.

## 7. Kiểm thử vòng 1 — 60 ca

Tạo:
- `docs/TEST_ROUND1_WEEK8.csv`
- `docs/TEST_ROUND1_WEEK8_SUMMARY.md`

CSV phải có đúng 13 trường:
`test_id,module,objective,precondition,input_data,steps,expected_result,actual_result,status,severity_if_fail,tester,executed_date,notes`

Không điền PASS nếu chưa chạy.

Phân bổ 60 ca:

| Mã | Mô-đun | Thuận | Ngoại lệ | Biên | Tổng |
|---|---|---:|---:|---:|---:|
| AUTH | Xác thực và phiên | 3 | 4 | 2 | 9 |
| PERM | Phân quyền | 2 | 4 | 1 | 7 |
| CATL | Danh mục/tìm kiếm/lọc/phân trang | 4 | 3 | 3 | 10 |
| BOOK | Booking/availability/experience booking | 3 | 4 | 3 | 10 |
| PAY | Thanh toán test mode | 2 | 3 | 1 | 6 |
| ORDR | Đơn hàng/trạng thái/hủy | 2 | 3 | 1 | 6 |
| ADMN | Quản trị/analytics/export | 3 | 2 | 1 | 6 |
| PYMD | Python recommendation/analytics | 1 | 2 | 0 | 3 |
| VALD | Server validation | 0 | 2 | 1 | 3 |
| **Tổng** | | **20** | **27** | **13** | **60** |

Trong 60 ca phải có tối thiểu 05 permission cases, 05 validation cases, đủ thuận/ngoại lệ/biên, có concurrency/conflict, có Python-down fallback.

Nếu PAY/ORDR chưa triển khai thì không bịa PASS: code để chạy được hoặc để BLOCKED/NOT EXECUTED kèm lý do.

## 8. Automated tests

Mở rộng PHPUnit cho business rule Week 8. Có thể chia:
- `Week8SellerProductTest`
- `Week8AvailabilityPricingTest`
- `Week8OrderPaymentTest`
- `Week8ReviewTest`

Chạy thật:

```bash
C:\xampp\php\php.exe artisan test --filter=M1AuthRoleSmokeTest
C:\xampp\php\php.exe artisan test --filter=M2BookingFlowTest
C:\xampp\php\php.exe artisan test --filter=M3PythonIntegrationTest
C:\xampp\php\php.exe artisan test --filter=Week8
C:\xampp\php\php.exe artisan test
python -m pytest -q
```

## 9. Browser compatibility / Playwright

Tạo `e2e/` nếu chưa có. Ưu tiên Playwright.

Thực thi 03 browser/engine: Chromium/Chrome, Firefox, WebKit hoặc Edge nếu cấu hình được; với 03 viewport 390, 768, 1440 => 9 tổ hợp.

Tối thiểu kiểm tra: `/`, `/tim-kiem`, `/san-pham-dia-phuong`, `/trai-nghiem`, login và dashboard phù hợp role.

Tạo `docs/BROWSER_MATRIX_WEEK8.csv` với:
`browser,viewport,route_or_flow,result,screenshot_path,notes`

Chỉ ghi PASS khi chạy thật. Nếu browser không cài được, ghi BLOCKED.

## 10. Bug log vòng 1

Tạo:
- `docs/BUG_LOG_WEEK8.csv`
- `docs/BUG_SUMMARY_WEEK8.md`

Các trường:
`bug_id,module,title,precondition,steps_to_reproduce,expected,actual,severity,priority,status,found_date,fixed_date,retest_result,fix_commit_or_file,notes`

Severity: Critical/High/Medium/Low.

Trước khi kết thúc Week 8:
- không còn Critical mở;
- High cố gắng fix hết;
- bug đã fix phải retest;
- không tạo bug giả.

## 11. Lighthouse / performance vòng 1

Nếu môi trường có Chrome/Lighthouse, đo 05 trang: Trang chủ, Tìm kiếm, Chi tiết sản phẩm, Trải nghiệm, Admin/Host dashboard.

Tạo `docs/PERFORMANCE_WEEK8.csv` với before/after: performance score, LCP, CLS, TBT, changes, notes.

Không bịa điểm. Nếu chưa chạy được, ghi NOT EXECUTED.

## 12. OWASP Top 10 vòng 1

Tạo `docs/OWASP_TOP10_WEEK8.md` và rà source thật:
- Broken Access Control
- Cryptographic Failures
- Injection
- Insecure Design
- Security Misconfiguration
- Vulnerable/Outdated Components
- Identification & Authentication Failures
- Software/Data Integrity Failures
- Security Logging/Monitoring Failures
- SSRF

Mỗi mục: route/file liên quan, cách kiểm, kết quả, fix nếu cần, evidence/test. Không tấn công hệ thống không thuộc nhóm.

## 13. Chương 3 — Thiết kế hệ thống

Tạo `docs/CHUONG_3_TUAN8_NHAP.md`, bám code/schema thật, đủ nội dung để chuyển sang Word khoảng 12–20 trang.

Cấu trúc bắt buộc:

### 3.1 Kiến trúc tổng thể
Sơ đồ ngữ cảnh + thành phần; Laravel 9 + MySQL + FastAPI; presentation/business/data; luận giải modular monolith + Python internal service; luồng request.

### 3.2 Thiết kế CSDL vật lý
21 bảng; PK/FK/CHECK/UNIQUE/INDEX; room availability; seasonal pricing; booking/status log; local products/orders; experiences; reviews; notifications/loyalty/audit; trigger/view hiện có. Không chép nguyên khối CREATE TABLE.

### 3.3 Thiết kế API/route
Route web chính; FastAPI endpoints; HTTP 200/201/302/401/403/409/422; validation/error; pagination/filter; Laravel↔Python.

### 3.4 Điều hướng và tổ chức trang
Site map; flow guest/host/seller/admin; master layout; route naming; URL hiện tại.

### 3.5 UX/UI
Design tokens; typography; components; 02 theme; responsive 390/768/1440; accessibility cơ bản.

### 3.6 Bảo mật
Auth/session/cookie; Hash/bcrypt; CSRF; validation; ORM/binding chống SQL injection; Blade escaping chống XSS; RBAC; ownership; Python token; OWASP risk table. Không tuyên bố HTTPS local nếu chưa có.

### 3.7 Python
Mục tiêu; input; TF-IDF; cosine; analytics; ETL; FastAPI; token; cache; timeout/retry/fallback; sơ đồ tích hợp.

### 3.8 Kết luận Chương 3

## 14. Diagrams cho Chương 3

Tạo trong `docs/diagrams/`:
- `architecture_context.mmd`
- `architecture_component.mmd`
- `sitemap.mmd`
- `guest_flow.mmd`
- `host_flow.mmd`
- `seller_flow.mmd`
- `admin_flow.mmd`
- `python_integration.mmd`

Nếu render được thì xuất PNG/SVG vào `docs/diagrams/rendered/`. Không render được thì ghi NOT RENDERED, không giả ảnh.

## 15. Health/log chuẩn bị Tuần 9

Nếu chưa có Laravel `/healthz`, thêm endpoint kiểm tra application + database + Python service, không lộ exception/secret.

Kiểm tra logging cho Python integration error, booking/order conflict và authorization quan trọng; không log password/token.

Có thể chuẩn bị `scripts/backup-db.ps1` và `scripts/restore-db-test.ps1`, nhưng không ghi backup đã kiểm chứng nếu chưa restore thật.

## 16. Repo hygiene

Rà `README.md`, `.gitignore`, `.env.example`; không có `.env` tracked; không có secret hard-code; hướng dẫn chạy Laravel/Python; dữ liệu demo phải ghi synthetic/project-seed.

Repo hiện mới có `main`. Không rewrite history. Nếu workflow cho phép, từ Tuần 8 tạo `dev` và `feature/week8-ui-testing`, làm việc thật trên branch rồi merge. Không tạo commit giả để bù lịch sử.

## 17. Screenshot list Week 8

Ghi vào `CODE_STATUS.md` danh sách user cần chụp:
1. Light theme desktop.
2. Theme thứ hai desktop.
3. Mobile 390 — search.
4. Tablet 768 — product/experience.
5. Desktop 1440 — admin dashboard.
6. Theme switch vẫn giữ sau reload.
7. Host availability/calendar.
8. Host seasonal pricing.
9. Seller product CRUD.
10. Product order/payment test mode nếu có.
11. Review hợp lệ và review bị chặn.
12. Playwright/browser matrix.
13. Full test output.
14. Bug log round 1.
15. Lighthouse 5 trang nếu đã chạy.
16. `/healthz` app/db/python nếu có.

## 18. Cập nhật PROJECT_CONTEXT và CODE_STATUS

Trong `PROJECT_CONTEXT.md`, thêm `## Week 8 implementation status` ghi chức năng, theme, responsive, test round1, bug summary, browser matrix, Chương 3, known issues.

Trong `CODE_STATUS.md`, ghi:
- Current milestone: Week 8 / Round 1 Review
- baseline `682aed4`
- features completed
- UI themes/responsive/footer/accessibility
- functional tests: designed/executed/pass/fail/blocked + module counts
- bug counts theo severity, fixed/open
- browser matrix thực tế
- Lighthouse thực tế hoặc NOT EXECUTED
- regression Python/M1/M2/M3/Week8/full suite
- Chapter 3 + diagrams
- known issues
- screenshots cần chụp
- Git branch/status/commit nếu có thật

## 19. Acceptance criteria

Chỉ báo Week 8 COMPLETE khi:
- gap matrix xong;
- Must còn thiếu đã code hoặc blocker rõ;
- availability/calendar + seasonal price dùng được;
- seller ownership bảo vệ;
- product/order/payment/review xử lý theo schema hoặc blocker rõ;
- 02 theme thật;
- responsive 390/768/1440;
- theme persist;
- footer học thuật;
- 60 test cases đủ 13 trường;
- đủ 3 loại kịch bản;
- >=5 permission + >=5 validation;
- test round1 đã thực thi trung thực;
- bug log thật, không còn Critical mở;
- browser matrix đã chạy hoặc blocker rõ;
- M1/M2/M3 + Python + full suite pass;
- Chương 3 đủ 8 mục;
- diagram source đủ;
- PROJECT_CONTEXT/CODE_STATUS cập nhật;
- không có secret trong Git.

## 20. Thứ tự thực hiện

1. Pull/inspect baseline.
2. Chạy regression trước sửa.
3. Lập gap matrix.
4. Hoàn thiện Must functions.
5. Theme system.
6. Responsive.
7. Footer/accessibility.
8. Week8 tests.
9. Tạo 60-case checklist.
10. Chạy round1.
11. Ghi bug log.
12. Fix Critical/High.
13. Retest.
14. Browser matrix.
15. Lighthouse nếu có.
16. OWASP review.
17. Viết Chương 3.
18. Tạo diagram source.
19. Full regression.
20. Cập nhật docs.
21. `git status --short`.
22. Báo cáo kết quả thật.

## 21. Response cuối của Codex

```text
Week 8 completed / partially completed.

1. Baseline and branch
- baseline:
- branch:

2. Features completed
- ...

3. UI
- themes:
- responsive:
- footer:

4. Test round 1
- designed:
- executed:
- passed:
- failed:
- blocked:
- module counts:

5. Bugs
- Critical:
- High:
- Medium:
- Low:
- fixed/open:

6. Browser matrix
- ...

7. Performance
- ... or NOT EXECUTED

8. Regression
- Python:
- M1:
- M2:
- M3:
- Week8:
- full suite:

9. Chapter 3
- file:
- diagrams:

10. Known issues
- ...

11. Screenshots user must capture
- ...

12. Git
- status:
- commit SHA only if real
```

Không ghi “complete” nếu 60 test cases, 2 themes, responsive hoặc Chương 3 chưa hoàn tất.
