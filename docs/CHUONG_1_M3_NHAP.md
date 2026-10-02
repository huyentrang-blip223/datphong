# CHƯƠNG 1 - TỔNG QUAN ĐỀ TÀI ĐT-07

## 1.1. Bối cảnh
Đề tài ĐT-07 xây dựng sàn lưu trú cộng đồng homestay gắn với sản phẩm địa phương. Hệ thống tập trung vào luồng lưu trú homestay, đồng thời mở rộng trải nghiệm du lịch thông qua sản phẩm địa phương và hoạt động văn hóa tại từng homestay.

## 1.2. Mục tiêu
- Cung cấp nền tảng đặt phòng homestay có kiểm soát tồn phòng theo ngày.
- Hỗ trợ host quản lý homestay, phòng, giá và tình trạng mở bán.
- Cho phép khách tìm kiếm phòng, đặt phòng và xem sản phẩm/trải nghiệm liên quan.
- Bổ sung Python service để gợi ý sản phẩm địa phương và cung cấp analytics cho quản trị.

## 1.3. Phạm vi hiện tại
Mốc M3 đang chạy trên Laravel 9, MySQL và FastAPI/Python. Phạm vi đã triển khai gồm:
- xác thực và phân quyền vai trò admin, guest, host, seller;
- CRUD homestay cho admin;
- quản lý phòng cho host;
- tìm kiếm phòng và tạo booking có chống double booking;
- danh sách/chi tiết sản phẩm địa phương;
- danh sách/chi tiết và đặt chỗ trải nghiệm văn hóa;
- admin dashboard có số liệu hệ thống và analytics từ Python;
- Python recommendation cho sản phẩm địa phương;
- ETL/làm sạch dữ liệu mẫu có log và thống kê.

## 1.4. Tác nhân
- `admin`: quản trị homestay, xem dashboard và analytics.
- `host`: quản lý homestay/phòng thuộc quyền sở hữu.
- `guest`: tìm kiếm phòng, đặt phòng, xem sản phẩm/trải nghiệm, đặt trải nghiệm.
- `seller`: chủ thể dữ liệu sản phẩm địa phương trong schema; CRUD seller đầy đủ chưa được mở rộng trong M3.

## 1.5. Kiến trúc hệ thống đang chạy
- Backend chính: Laravel 9 (`laravel/framework ^9.19`).
- Database: MySQL, schema ĐT-07 gồm 21 bảng nghiệp vụ.
- Giao diện: Blade views dùng layout chung.
- Python module: FastAPI service trong `python-service`, chạy local bằng `uvicorn app.main:app --host 127.0.0.1 --port 8001`.
- Tích hợp Laravel -> Python: `PythonDataService` gọi HTTP với header `X-Service-Token`, timeout 3 giây, retry 2 lần, cache 30 phút và fallback bằng Eloquent khi Python lỗi.

## 1.6. Vai trò Python
Python không đứng riêng như script minh họa. Service Python được Laravel gọi để:
- gợi ý sản phẩm địa phương trên trang chi tiết sản phẩm;
- cung cấp analytics công suất theo tháng;
- cung cấp top homestay theo booking/doanh thu cho dashboard admin;
- chạy ETL dry-run tạo file clean và stats phục vụ phân tích dữ liệu.

## 1.7. Luồng tích hợp Laravel và Python
1. Người dùng mở chi tiết sản phẩm địa phương trong Laravel.
2. Laravel gọi `GET /recommend/local-products/{product_id}` của FastAPI với token nội bộ.
3. Python dùng dữ liệu `local_products`, `homestay_products`, `homestays`, tạo hồ sơ nội dung và tính TF-IDF/cosine similarity.
4. Laravel hiển thị kết quả recommendation; nếu Python timeout/down thì dùng fallback Eloquent.
5. Admin dashboard gọi analytics Python; nếu Python lỗi, dashboard vẫn render empty state.

## 1.8. Giới hạn hiện tại
- Runtime data đạt 583 bản ghi du lịch chính, không phải bộ dữ liệu mục tiêu 3.850 bản ghi.
- CRUD seller cho sản phẩm địa phương chưa mở rộng đầy đủ; M3 ưu tiên browsing, recommendation, experience booking và admin analytics.
- FastAPI token trong `.env` local dùng cho kiểm chứng, không đưa token thật vào `.env.example`.
- Python service đang dùng PyMySQL trực tiếp vì SQLAlchemy C-extension bị Windows Application Control chặn trong môi trường chạy test.
