# CHƯƠNG 2 - CƠ SỞ DỮ LIỆU, XỬ LÝ DỮ LIỆU VÀ KIỂM CHỨNG

## 2.1. Nguồn và trạng thái dữ liệu
Runtime MySQL `dt07_homestay` hiện có dữ liệu mô phỏng deterministic từ `M2TourismDatasetSeeder`. Dữ liệu có nguồn gốc `synthetic / project seed`, không phải dữ liệu khảo sát thật.

Exact COUNT runtime:

| Bảng | Số dòng |
|---|---:|
| homestays | 15 |
| rooms | 36 |
| room_availability | 360 |
| seasonal_prices | 36 |
| bookings | 24 |
| local_products | 24 |
| homestay_products | 24 |
| product_orders | 20 |
| product_order_items | 40 |
| experiences | 24 |
| experience_bookings | 24 |
| reviews | 20 |
| tourism_total chính | 583 |

## 2.2. Cấu trúc dữ liệu chính
- `homestays` lưu thông tin homestay và chủ sở hữu.
- `rooms` tách riêng khỏi homestay, lưu loại phòng, sức chứa, giá cơ bản và số lượng.
- `room_availability` lưu tồn phòng theo ngày gồm `units_total`, `units_held`, `units_sold`, `price_override`, `status`.
- `seasonal_prices` lưu giá theo khoảng ngày, không ghi đè giá cơ bản của phòng.
- `bookings` và `booking_status_logs` lưu đặt phòng và lịch sử trạng thái.
- `local_products`, `homestay_products`, `product_orders`, `product_order_items` lưu sản phẩm địa phương và liên kết với homestay.
- `experiences`, `experience_bookings` lưu trải nghiệm văn hóa và đặt chỗ.

## 2.3. Quy tắc availability và giá phòng
Tồn khả dụng theo ngày:

```text
available_units = units_total - units_held - units_sold
```

Khi đặt phòng, Laravel dùng transaction và `lockForUpdate()` theo thứ tự `stay_date` để chống double booking. Check-out được xử lý exclusive khi tính số đêm.

Thứ tự ưu tiên giá:

```text
room_availability.price_override -> seasonal_prices.price_per_night -> rooms.base_price
```

## 2.4. ETL cleaning
Script `python-service/app/etl_clean.py` nhận `--input`, `--output`, `--dry-run` và thực hiện:
- chuẩn hóa tên cột;
- trim text và chuẩn hóa khoảng trắng;
- chuyển kiểu số an toàn cho `price`, `stock_qty`;
- loại giá/tồn kho âm;
- loại duplicate theo `name + origin_place`;
- xuất file clean và file stats JSON;
- ghi log số dòng trước/sau và thống kê mô tả.

ETL dry-run đã chạy với:

```bash
python -m app.etl_clean --input data/raw/local_products_seed.csv --output data/clean/local_products_clean.csv --dry-run
```

Kết quả thật:
- rows_before: 6
- rows_after: 6
- rows_removed: 0
- price mean: 55,500
- stock_qty mean: 103.5

## 2.5. Thuật toán recommendation
Python service tạo recommendation sản phẩm địa phương bằng content-based filtering:
- dữ liệu đầu vào từ `local_products`, `homestay_products`, `homestays`;
- hồ sơ nội dung gồm `name`, `origin_place`, `description`, tên homestay và tỉnh;
- vector hóa bằng TF-IDF với unigram/bigram;
- tính cosine similarity;
- loại chính sản phẩm đang xem;
- sắp xếp theo điểm tương đồng, phụ trợ bởi khoảng cách giá;
- cache ma trận TF-IDF in-memory và có endpoint `POST /cache/refresh`.

Ví dụ endpoint thật đã chạy:

```text
GET /recommend/local-products/25?k=3
```

Trả về 3 sản phẩm gợi ý và không trả lại `product_id=25`.

## 2.6. Analytics
Python analytics cung cấp:
- `GET /analytics/seasonality`: công suất theo tháng từ `room_availability`.
- `GET /analytics/top-homestays`: top homestay theo số booking/doanh thu từ `bookings`, `rooms`, `homestays`.

Kết quả thật của seasonality:
- month: `2026-10`
- units_total: 960
- units_sold: 24
- occupancy_rate: 0.025

## 2.7. Kiểm thử đã thực thi
Python:
- `python -m pytest -q` -> 5 passed, 2 warnings.

Laravel:
- `php artisan test --filter=M1AuthRoleSmokeTest` -> 6 passed.
- `php artisan test --filter=M2BookingFlowTest` -> 9 passed.
- `php artisan test --filter=M3PythonIntegrationTest` -> 6 passed.
- `php artisan test` -> 23 passed.

Kiểm chứng tích hợp thật:
- FastAPI `/healthz` trả `{"status":"ok"}`.
- FastAPI recommendation trả JSON hợp lệ.
- Laravel trang `/san-pham-dia-phuong/25` trả 200 và hiển thị nguồn `Python service`.
- Khi trỏ Python URL sang cổng không có service, cùng trang vẫn trả 200 và hiển thị `Laravel fallback`.
- Admin dashboard sau login trả 200, hiển thị analytics từ `Python service`, có tháng `2026-10` và bảng top homestay.
