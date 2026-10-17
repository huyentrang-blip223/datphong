# NEXT_TASK_WEEK7_M3_FULL_DT07.md

## CSE703073 — ĐT-07 — Tuần 7 / Tuần học kỳ 12 / Mốc M3

**Đề tài:** Sàn lưu trú cộng đồng homestay gắn với sản phẩm địa phương  
**Repository:** `huyentrang-blip223/datphong`  
**Branch mặc định hiện tại:** `main`  
**Commit M2 gần nhất cần kế thừa:** `2cd738c` — `Implement M2 booking flow`

---

# 0. CÁCH LÀM VIỆC BẮT BUỘC

Trước khi sửa code, hãy đọc toàn bộ:

1. `AGENTS.md`
2. `docs/PROJECT_CONTEXT.md`
3. `docs/CODE_STATUS.md`
4. `database/schema_DT07.sql`
5. `backend/composer.json`
6. toàn bộ source M2 liên quan tới rooms / availability / booking
7. file task này

Sau đó **inspect repository trước, không thiết kế lại từ đầu**.

Không nâng Laravel trong task này. Source đang chạy thực tế là **Laravel 9 (`laravel/framework ^9.19`)**; phải giữ tài liệu và code khớp phiên bản chạy thật.

Không tự tuyên bố test PASS, dữ liệu >=300, Python tích hợp thành công, Git commit hoặc screenshot nếu chưa thực thi thật.

---

# 1. MỤC TIÊU CHÍNH TUẦN 7 / M3

M3 phải hoàn thành đồng thời các nhóm sau:

1. **Nhóm nghiệp vụ trọng tâm thứ hai** của ĐT-07.
2. **Mô-đun xử lý dữ liệu bằng Python** giải quyết bài toán dữ liệu thật của đề tài.
3. Python phải **được tích hợp vào hệ thống**, không phải file `.py` đứng riêng.
4. Có **bảng điều khiển quản trị** sử dụng kết quả phân tích.
5. Có **timeout + cache + fallback** khi Laravel gọi Python.
6. Có **ETL/làm sạch dữ liệu chạy lại được**, ghi log và thống kê.
7. Có **tối thiểu 02 ca kiểm thử Python**; mục tiêu task này là nhiều hơn 02.
8. Không làm hỏng M1/M2.
9. Hoàn thiện tài liệu kỹ thuật để ChatGPT có thể dựng **bản thảo Chương 1 và Chương 2** đúng với code/runtime thật.
10. Giải quyết blocker của M2: database runnable phải có **>=300 bản ghi dữ liệu du lịch** trước khi chốt M3.

---

# 2. BLOCKER PHẢI XỬ LÝ TRƯỚC: DỮ LIỆU M2

`docs/CODE_STATUS.md` hiện ghi database `dt07_homestay` chỉ có:

- `homestays = 3`
- `rooms = 0`
- `room_availability = 0`
- `seasonal_prices = 0`
- `bookings = 0`
- `tourism_total = 3`

Trong khi mốc M2/M3 cần dữ liệu runnable đủ để demo.

## 2.1. Yêu cầu

Trước khi tuyên bố M3 hoàn thành:

- kiểm tra repository có seed/dataset mới hay không;
- nếu đã có seed tương thích schema hiện tại thì dùng nó;
- nếu chưa có, **tạo một seeder/dataset mô phỏng có nguồn gốc rõ ràng**, deterministic, tái lập được và tương thích `database/schema_DT07.sql`;
- không gọi dữ liệu mô phỏng là dữ liệu khảo sát thật;
- không phá dữ liệu tài khoản M1 đang dùng;
- không chạy full Laravel starter migrations lên Week 4 schema một cách mù quáng.

## 2.2. Ngưỡng

Sau khi nạp, chạy COUNT thật cho ít nhất:

- `homestays`
- `rooms`
- `room_availability`
- `seasonal_prices`
- `bookings`
- `local_products`
- `homestay_products`
- `experiences`
- `experience_bookings`
- `reviews`

Tổng dữ liệu du lịch dùng trong hệ thống phải **>=300**.

Ghi exact count thật vào `docs/CODE_STATUS.md`.

---

# 3. NHÓM NGHIỆP VỤ TRỌNG TÂM THỨ HAI

ĐT-07 khác biệt ở **sản phẩm địa phương + trải nghiệm văn hóa gắn với homestay**.

Tuần 7 phải triển khai tối thiểu phần sau dựa trên đúng các cột/bảng hiện có trong `schema_DT07.sql`.

## 3.1. Local products

Hoàn thiện luồng cơ bản:

- khách xem danh sách sản phẩm địa phương;
- lọc/tìm kiếm hợp lý theo dữ liệu thực tế có trong schema;
- xem chi tiết sản phẩm;
- hiển thị homestay liên quan nếu có `homestay_products`;
- chỉ hiển thị sản phẩm đang hoạt động/published theo đúng cột trạng thái của schema;
- tránh hard-code field không tồn tại: phải inspect schema trước.

Nếu module seller đã đủ dữ liệu/quyền trong schema, bổ sung CRUD tối thiểu cho seller. Nếu schema/role chưa đủ để làm CRUD seller an toàn trong tuần này, ghi rõ vào `CODE_STATUS` và ưu tiên browsing + recommendation + admin dashboard.

## 3.2. Experiences

Hoàn thiện tối thiểu:

- danh sách trải nghiệm;
- xem trải nghiệm gắn homestay;
- kiểm tra capacity/availability theo đúng schema;
- nếu flow `experience_bookings` hiện đủ cột và quan hệ, cho guest đặt trải nghiệm;
- nếu chưa đủ điều kiện cho UI booking hoàn chỉnh, phải có service/model/query + test nghiệp vụ, không bịa chức năng.

## 3.3. Không làm lệch phạm vi

Core vẫn là:

`homestay -> room -> booking`

Tuần 7 mở rộng:

`homestay -> local products / experiences -> Python recommendation / analytics`

Không thay booking accommodation bằng product booking.

---

# 4. PYTHON SERVICE — BẮT BUỘC

Tạo thư mục rõ ràng, ưu tiên:

```text
python-service/
├── app/
│   ├── __init__.py
│   ├── main.py
│   ├── db.py
│   ├── recommender.py
│   ├── analytics.py
│   └── etl_clean.py
├── data/
│   ├── raw/
│   └── clean/
├── tests/
├── requirements.txt
├── .env.example
└── README.md
```

Không commit `.venv`, secret, password DB hoặc token thật.

## 4.1. requirements.txt

Dùng phiên bản tương thích với Python đang cài thực tế. Các thư viện dự kiến:

- FastAPI
- uvicorn
- pandas
- numpy
- scikit-learn
- SQLAlchemy
- PyMySQL
- python-dotenv
- pydantic
- pytest
- httpx nếu cần cho FastAPI TestClient

Không copy version mù quáng nếu Python local không tương thích. Sau khi cài, ghi phiên bản thật vào `CODE_STATUS`.

---

# 5. BÀI TOÁN PYTHON CHÍNH: GỢI Ý SẢN PHẨM ĐỊA PHƯƠNG

Triển khai recommendation có ý nghĩa trực tiếp với ĐT-07.

## 5.1. Input

Dựa trên `local_products`, `homestay_products`, `homestays` và các trường mô tả/danh mục/giá thực tế có trong schema.

**Phải inspect schema trước.**

Không được giả định cột `title`, `category_id`, `description` tồn tại nếu schema dùng tên khác.

## 5.2. Phương pháp

Ưu tiên content-based đơn giản, giải thích được:

- tạo hồ sơ nội dung từ các trường text có thật;
- TF-IDF;
- cosine similarity;
- có thể thêm filter khoảng giá/homestay/tỉnh nếu dữ liệu đủ;
- kết quả trả về có `product_id` và điểm tương đồng;
- loại chính sản phẩm hiện tại khỏi recommendation;
- xử lý dataset nhỏ/field rỗng an toàn.

Nếu dữ liệu thực tế chưa đủ để TF-IDF hoạt động ổn, tạo fallback Python đơn giản và ghi lý do.

## 5.3. Cache Python

Không tính lại ma trận TF-IDF cho mọi request.

Có cache in-memory đơn giản và endpoint refresh.

---

# 6. PYTHON ANALYTICS CHO ADMIN DASHBOARD

Tạo module analytics dựa trên đúng schema ĐT-07.

Tối thiểu 2 thống kê thực tế, ví dụ:

1. **Occupancy / công suất theo tháng** dựa vào `room_availability`.
2. **Top homestays / top provinces / số booking** dựa vào `bookings`, `rooms`, `homestays`.

Nếu field/schema khác, điều chỉnh query theo schema.

Không sử dụng query mẫu của tài liệu nếu tên bảng/cột không khớp.

Kết quả phải ở dạng JSON có thể hiển thị trong dashboard.

---

# 7. ETL / DATA CLEANING — BẮT BUỘC

Tạo `python-service/app/etl_clean.py`.

## 7.1. Yêu cầu

Script phải:

- chạy lại được;
- nhận input rõ ràng (`--input`);
- có `--dry-run`;
- chuẩn hóa tên cột;
- xử lý missing/null;
- trim/chuẩn hóa khoảng trắng;
- chuẩn hóa text cần thiết;
- chuyển kiểu số an toàn;
- loại hoặc flag dữ liệu vô lý theo business rule;
- loại duplicate theo key phù hợp;
- ghi số lượng trước/sau;
- xuất file clean hoặc upsert DB tùy mode;
- ghi log;
- in thống kê mô tả dùng được cho Chương 2.

## 7.2. Dữ liệu nguồn

Nếu không có nguồn CSV thật trong repo:

- tạo file raw từ dataset mô phỏng đã seed;
- ghi rõ `source_note = synthetic / project seed`;
- không được gọi là dữ liệu thu thập từ người dùng thật.

---

# 8. FASTAPI ENDPOINTS

Tối thiểu:

```text
GET  /healthz
GET  /recommend/local-products/{product_id}?k=6
GET  /analytics/seasonality
GET  /analytics/top-homestays?limit=10
POST /cache/refresh
```

## 8.1. Bảo vệ nội bộ

Dùng header:

```text
X-Service-Token
```

Token đọc từ `.env`.

- `/healthz` có thể public local;
- các endpoint dữ liệu phải kiểm token;
- token sai -> 401;
- không log token thật.

---

# 9. TÍCH HỢP LARAVEL ↔ PYTHON

Tạo/hoàn thiện:

```text
backend/app/Services/PythonDataService.php
backend/config/services.php
backend/.env.example
```

`.env.example` cần có:

```env
PY_SERVICE_URL=http://127.0.0.1:8001
PY_SERVICE_TOKEN=
PY_SERVICE_TIMEOUT=3
```

Không commit token thật vào `.env.example`.

## 9.1. PythonDataService bắt buộc có

- request timeout khoảng 3 giây;
- retry hợp lý, ví dụ 2 lần;
- cache khoảng 30 phút;
- log warning/error khi Python lỗi;
- fallback phía Laravel.

## 9.2. Fallback recommendation

Khi Python tắt hoặc timeout:

- trang **không được HTTP 500**;
- fallback dùng query Laravel/Eloquent trên local products;
- ưu tiên cùng homestay/category và khoảng giá nếu schema hỗ trợ;
- nếu không đủ dữ liệu thì trả danh sách rỗng có kiểm soát.

## 9.3. UI tích hợp

Hiển thị recommendation trên **một trang thật của hệ thống**, ví dụ:

- chi tiết sản phẩm địa phương; hoặc
- chi tiết homestay với mục “Sản phẩm địa phương gợi ý”.

Không chỉ test bằng curl rồi bỏ đó.

---

# 10. ADMIN DASHBOARD M3

M3 yêu cầu có bảng điều khiển quản trị.

Tận dụng admin dashboard hiện tại hoặc tạo trang con rõ ràng.

Dashboard tối thiểu hiển thị:

- số homestay;
- số room;
- số booking;
- số local products;
- 1 khối analytics lấy từ Python, ví dụ occupancy theo tháng;
- 1 bảng top homestays / top khu vực.

Nếu Python unavailable:

- dashboard vẫn render;
- khối Python hiển thị fallback/empty state;
- không crash toàn trang.

---

# 11. TEST — BẮT BUỘC THỰC THI

## 11.1. Python tests

Tạo tests tối thiểu:

1. `/healthz` -> 200
2. endpoint protected thiếu/sai token -> 401
3. recommendation endpoint trả schema hợp lệ
4. recommendation không trả chính product đang hỏi
5. analytics endpoint trả JSON hợp lệ
6. ETL `--dry-run` tạo clean output / stats

Không cần test tất cả nếu môi trường hạn chế, nhưng **tối thiểu 02 ca Python phải chạy PASS**.

## 11.2. Laravel integration tests

Tối thiểu:

1. Python service success -> recommendation được trả về
2. Python service timeout/error -> fallback chạy, trang không 500
3. cache được dùng hoặc key được tạo đúng
4. admin dashboard vẫn render khi Python down
5. regression M2 booking vẫn pass
6. regression M1 RBAC vẫn pass

Sử dụng `Http::fake()` cho integration test Laravel nếu phù hợp.

## 11.3. Full regression

Chạy:

```bash
php artisan test --filter=M1AuthRoleSmokeTest
php artisan test --filter=M2BookingFlowTest
php artisan test --filter=M3
php artisan test
```

Tên test M3 tùy implementation, nhưng phải rõ.

Python:

```bash
pytest -q
```

Nếu không chạy được command nào, ghi `NOT EXECUTED` cùng lý do.

---

# 12. KIỂM CHỨNG TÍCH HỢP THẬT

Sau khi chạy cả Laravel và FastAPI:

## 12.1. Python

```bash
curl http://127.0.0.1:8001/healthz
```

Gọi recommendation với token thật ở local, **không đưa token vào CODE_STATUS**.

## 12.2. Laravel

Kiểm tra route/page thật gọi Python.

Sau đó **tắt FastAPI**, refresh cùng trang và xác nhận:

- Laravel vẫn trả 200;
- fallback có dữ liệu hoặc empty state;
- không có lỗi 500.

Ghi kết quả thực tế.

---

# 13. M3 EVIDENCE PHẢI CHỤP

Codex không cần tự tạo ảnh nếu không có browser tool, nhưng phải ghi chính xác danh sách user cần chụp vào `docs/CODE_STATUS.md`.

Tối thiểu:

1. FastAPI `/healthz`.
2. Recommendation endpoint JSON.
3. Recommendation hiển thị trên giao diện Laravel.
4. Admin dashboard có analytics Python.
5. Dashboard/recommendation vẫn chạy khi FastAPI tắt, chứng minh fallback.
6. Terminal `pytest -q`.
7. Terminal M3 Laravel tests.
8. Terminal full `php artisan test`.
9. ETL dry-run log: số bản ghi trước/sau + thống kê.
10. COUNT dữ liệu runtime `>=300`.
11. Git commit M3 sau khi user commit/push.

---

# 14. TÀI LIỆU KỸ THUẬT CHO CHƯƠNG 1 VÀ CHƯƠNG 2

Tạo hai file Markdown để ChatGPT chuyển thành Word sau:

```text
docs/CHUONG_1_M3_NHAP.md
docs/CHUONG_2_M3_NHAP.md
```

Không viết văn chung chung. Phải dựa trên source/runtime thật.

## 14.1. `CHUONG_1_M3_NHAP.md`

Ghi:

- bối cảnh ĐT-07;
- mục tiêu;
- phạm vi;
- tác nhân;
- nhóm chức năng;
- kiến trúc hệ thống đang chạy;
- stack thật: Laravel 9, MySQL, FastAPI/Python nếu đã chạy;
- vai trò Python;
- luồng tích hợp Laravel ↔ Python;
- các giới hạn hiện tại.

## 14.2. `CHUONG_2_M3_NHAP.md`

Ghi:

- nguồn và trạng thái dữ liệu;
- exact COUNT runtime;
- cấu trúc dữ liệu chính;
- quy tắc room availability;
- giá override -> seasonal -> base;
- ETL cleaning steps;
- thống kê ETL thật;
- mô tả thuật toán recommendation;
- cách đánh giá/kiểm chứng recommendation;
- các test M1/M2/M3 đã thực thi và số PASS thật.

Không ghi 3.850 là runtime nếu database hiện không có 3.850.

---

# 15. CẬP NHẬT PROJECT CONTEXT

Sau khi làm xong, cập nhật:

```text
docs/PROJECT_CONTEXT.md
```

Thêm phần:

```text
## Week 7 / M3 implementation status
```

Ghi chính xác:

- second business module;
- Python module;
- API endpoints;
- Laravel integration;
- timeout/cache/fallback;
- admin dashboard;
- ETL;
- runtime counts;
- tests actually run;
- known issues.

---

# 16. CẬP NHẬT CODE_STATUS — BẮT BUỘC

Cuối task cập nhật:

```text
docs/CODE_STATUS.md
```

Phải có các mục:

## Current milestone
Week 7 / M3

## What was implemented
Liệt kê thật.

## Files changed
Tất cả file chính.

## Database/schema changes
Ghi rõ có/không.

## Data load evidence
Bảng exact COUNT runtime.

## Python environment
- `python --version`
- package versions chính
- command chạy FastAPI

## Python endpoints
Liệt kê.

## Laravel routes/pages added
Liệt kê.

## Commands actually run
Copy command thật.

## Tests actually executed

Bảng:

| Test/command | Result |
|---|---|
| pytest | ... |
| M1 smoke | ... |
| M2 flow | ... |
| M3 integration | ... |
| full Laravel suite | ... |

## Integration evidence
- FastAPI up
- Laravel calls Python
- Python down -> fallback

## Known issues
Không che lỗi.

## Screenshot list
Danh sách cần chụp.

## Git evidence
Nếu chưa commit thì ghi chưa commit; không bịa SHA.

---

# 17. ACCEPTANCE CRITERIA — CHỈ ĐƯỢC GHI M3 HOÀN THÀNH KHI

- [ ] M2 data blocker đã xử lý, runtime >=300 records
- [ ] nhóm nghiệp vụ thứ hai có route/UI/code chạy thật
- [ ] Python service chạy được
- [ ] Python giải quyết bài toán dữ liệu của ĐT-07
- [ ] Python được tích hợp vào Laravel
- [ ] recommendation/analytics xuất hiện trên UI
- [ ] timeout có cấu hình
- [ ] cache có cấu hình
- [ ] fallback hoạt động khi Python dừng
- [ ] ETL rerunnable
- [ ] ETL có log + descriptive stats
- [ ] >=2 Python tests PASS
- [ ] M3 Laravel integration tests PASS
- [ ] M2 tests vẫn PASS
- [ ] M1 tests vẫn PASS
- [ ] full suite PASS
- [ ] admin dashboard có dữ liệu/analytics
- [ ] `CHUONG_1_M3_NHAP.md` hoàn thành
- [ ] `CHUONG_2_M3_NHAP.md` hoàn thành
- [ ] `PROJECT_CONTEXT.md` cập nhật
- [ ] `CODE_STATUS.md` cập nhật
- [ ] không có secret/token/password thật trong Git

Nếu một mục bắt buộc chưa làm được, **không đánh dấu hoàn thành**. Ghi blocker cụ thể.

---

# 18. THỨ TỰ TRIỂN KHAI KHUYẾN NGHỊ

Làm theo thứ tự để tránh vỡ project:

1. Inspect schema/source.
2. Chốt/nạp dataset >=300.
3. Chạy regression M1/M2 trước khi sửa.
4. Implement local products / experiences tối thiểu.
5. Tạo `python-service`.
6. Implement ETL.
7. Implement recommender.
8. Implement analytics.
9. Chạy Python tests.
10. Tích hợp `PythonDataService` vào Laravel.
11. Hiển thị recommendation trên UI.
12. Hiển thị analytics ở admin dashboard.
13. Test Python down / fallback.
14. Chạy M1 + M2 + M3 + full test.
15. Chạy COUNT runtime.
16. Cập nhật Chương 1/2 Markdown.
17. Cập nhật PROJECT_CONTEXT + CODE_STATUS.
18. Báo cáo lại chính xác kết quả.

---

# 19. RESPONSE CUỐI CỦA CODEX

Khi hoàn thành, trả lời user theo format này:

```text
M3 implementation completed / partially completed.

1. Files changed
- ...

2. Runtime data counts
- homestays: ...
- rooms: ...
- room_availability: ...
- local_products: ...
- ...
- tourism_total: ...

3. Python
- version:
- service URL:
- endpoints:
- ETL result:
- recommendation method:

4. Tests actually run
- pytest: ...
- M1: ...
- M2: ...
- M3: ...
- full suite: ...

5. Integration proof
- Python up:
- Laravel recommendation:
- Admin analytics:
- Python down fallback:

6. Known issues
- ...

7. Exact screenshots user must capture
- ...

8. Git status
- committed/pushed or not
- commit SHA only if it really exists
```

Không viết “done” nếu data >=300, Python integration, fallback hoặc tests vẫn chưa đạt.
